<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Studio;
use App\Notifications\DepositPaidNotification;
use App\Notifications\DepositRefundedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\StripeClient;

class PaymentService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret'));
        $this->stripe = new StripeClient(config('stripe.secret'));
    }

    /**
     * Create a Stripe Checkout Session for deposit payment.
     *
     * @param Appointment $appointment The appointment requiring deposit
     * @param string $successUrl URL to redirect on successful payment
     * @param string $cancelUrl URL to redirect on cancelled payment
     * @return Session The created Stripe Checkout Session
     * @throws ApiErrorException
     */
    public function createDepositCheckout(
        Appointment $appointment,
        string $successUrl,
        string $cancelUrl
    ): Session {
        $studio = $appointment->studio;
        $depositAmount = $this->calculateDepositAmount($appointment, $studio);

        // Convert to cents for Stripe
        $amountInCents = (int) round($depositAmount * 100);

        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => config('stripe.currency', 'usd'),
                    'product_data' => [
                        'name' => 'Appointment Deposit',
                        'description' => $this->buildDepositDescription($appointment, $studio),
                    ],
                    'unit_amount' => $amountInCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'customer_email' => $appointment->client_email,
            'metadata' => [
                'appointment_id' => $appointment->id,
                'studio_id' => $studio->id,
                'client_name' => $appointment->client_name,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'appointment_id' => $appointment->id,
                    'studio_id' => $studio->id,
                ],
            ],
        ]);

        // Store the checkout session ID on the appointment
        $appointment->update([
            'stripe_checkout_session_id' => $session->id,
            'deposit_amount' => $depositAmount,
        ]);

        Log::info('Stripe Checkout Session created', [
            'session_id' => $session->id,
            'appointment_id' => $appointment->id,
            'amount' => $depositAmount,
        ]);

        return $session;
    }

    /**
     * Handle a successful payment from webhook.
     *
     * @param string $sessionId The Stripe Checkout Session ID
     * @return Appointment|null The updated appointment, or null if not found
     * @throws ApiErrorException
     */
    public function handleSuccessfulPayment(string $sessionId): ?Appointment
    {
        // Retrieve the session to get payment details
        $session = $this->stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => ['payment_intent'],
        ]);

        $appointmentId = $session->metadata->appointment_id ?? null;

        if (!$appointmentId) {
            Log::warning('No appointment_id in session metadata', [
                'session_id' => $sessionId,
            ]);
            return null;
        }

        $appointment = Appointment::withoutGlobalScopes()->find($appointmentId);

        if (!$appointment) {
            Log::warning('Appointment not found for payment', [
                'session_id' => $sessionId,
                'appointment_id' => $appointmentId,
            ]);
            return null;
        }

        // Already processed
        if ($appointment->deposit_paid_at) {
            Log::info('Payment already processed', [
                'appointment_id' => $appointmentId,
                'session_id' => $sessionId,
            ]);
            return $appointment;
        }

        // Update appointment with payment details
        $paymentIntentId = $session->payment_intent instanceof PaymentIntent
            ? $session->payment_intent->id
            : $session->payment_intent;

        $appointment->update([
            'deposit_paid_at' => now(),
            'payment_method' => Appointment::PAYMENT_METHOD_STRIPE,
            'payment_reference' => $session->id,
            'stripe_payment_intent_id' => $paymentIntentId,
        ]);

        Log::info('Payment processed successfully', [
            'appointment_id' => $appointmentId,
            'session_id' => $sessionId,
            'payment_intent_id' => $paymentIntentId,
        ]);

        // Send notification (if implemented)
        try {
            $appointment->notify(new DepositPaidNotification($appointment));
        } catch (\Throwable $e) {
            Log::warning('Failed to send deposit paid notification', [
                'appointment_id' => $appointmentId,
                'error' => $e->getMessage(),
            ]);
        }

        return $appointment;
    }

    /**
     * Mark an appointment as manually paid (cash, Venmo, etc.).
     *
     * @param Appointment $appointment The appointment to mark as paid
     * @param string $method The payment method used
     * @param string|null $reference Optional payment reference/note
     * @return Appointment The updated appointment
     */
    public function markAsPaid(
        Appointment $appointment,
        string $method,
        ?string $reference = null
    ): Appointment {
        $studio = $appointment->studio;
        $depositAmount = $appointment->deposit_amount ?? $this->calculateDepositAmount($appointment, $studio);

        $appointment->update([
            'deposit_amount' => $depositAmount,
            'deposit_paid_at' => now(),
            'payment_method' => $method,
            'payment_reference' => $reference,
        ]);

        Log::info('Manual payment recorded', [
            'appointment_id' => $appointment->id,
            'method' => $method,
            'amount' => $depositAmount,
        ]);

        // Send notification (if implemented)
        try {
            $appointment->notify(new DepositPaidNotification($appointment));
        } catch (\Throwable $e) {
            Log::warning('Failed to send deposit paid notification', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $appointment->fresh();
    }

    /**
     * Refund a deposit via Stripe.
     * Uses database transaction to ensure consistency between Stripe and local DB.
     *
     * @param Appointment $appointment The appointment to refund
     * @param string|null $reason Optional reason for the refund
     * @return bool Whether the refund was successful
     */
    public function refundDeposit(Appointment $appointment, ?string $reason = null): bool
    {
        if (!$appointment->canBeRefunded()) {
            Log::warning('Appointment cannot be refunded', [
                'appointment_id' => $appointment->id,
                'is_deposit_paid' => $appointment->is_deposit_paid,
                'is_refunded' => $appointment->is_refunded,
                'is_stripe_payment' => $appointment->is_stripe_payment,
            ]);
            return false;
        }

        try {
            // Start transaction for database operations
            return DB::transaction(function () use ($appointment, $reason) {
                // Call Stripe API to create refund
                $refund = $this->stripe->refunds->create([
                    'payment_intent' => $appointment->stripe_payment_intent_id,
                    'reason' => $this->mapRefundReason($reason),
                    'metadata' => [
                        'appointment_id' => $appointment->id,
                        'studio_id' => $appointment->studio_id,
                        'custom_reason' => $reason,
                    ],
                ]);

                // Convert refunded amount from cents
                $refundedAmount = $refund->amount / 100;

                // Record refund in database (inside transaction)
                $appointment->recordRefund($refundedAmount, $reason);

                Log::info('Refund processed successfully', [
                    'appointment_id' => $appointment->id,
                    'refund_id' => $refund->id,
                    'amount' => $refundedAmount,
                ]);

                // Send notification (outside critical path - failure won't rollback)
                try {
                    $appointment->notify(new DepositRefundedNotification($appointment));
                } catch (\Throwable $e) {
                    Log::warning('Failed to send refund notification', [
                        'appointment_id' => $appointment->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                return true;
            });
        } catch (ApiErrorException $e) {
            Log::error('Stripe refund failed', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        } catch (\Exception $e) {
            // Log if DB update failed after Stripe refund succeeded
            Log::error('Refund recorded in Stripe but database update failed', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a refund event from Stripe webhook.
     *
     * @param string $paymentIntentId The Stripe Payment Intent ID
     * @param int $refundedAmountCents The refunded amount in cents
     * @return Appointment|null The updated appointment, or null if not found
     */
    public function handleRefundEvent(string $paymentIntentId, int $refundedAmountCents): ?Appointment
    {
        $appointment = Appointment::withoutGlobalScopes()
            ->where('stripe_payment_intent_id', $paymentIntentId)
            ->first();

        if (!$appointment) {
            Log::warning('Appointment not found for refund', [
                'payment_intent_id' => $paymentIntentId,
            ]);
            return null;
        }

        // Already refunded
        if ($appointment->refunded_at) {
            Log::info('Refund already recorded', [
                'appointment_id' => $appointment->id,
                'payment_intent_id' => $paymentIntentId,
            ]);
            return $appointment;
        }

        $refundedAmount = $refundedAmountCents / 100;
        $appointment->recordRefund($refundedAmount, 'Refunded via Stripe');

        Log::info('Refund recorded from webhook', [
            'appointment_id' => $appointment->id,
            'amount' => $refundedAmount,
        ]);

        return $appointment;
    }

    /**
     * Get the payment status for an appointment.
     *
     * @param Appointment $appointment The appointment to check
     * @return array Payment status information
     */
    public function getPaymentStatus(Appointment $appointment): array
    {
        $status = [
            'deposit_required' => $this->calculateDepositAmount($appointment, $appointment->studio),
            'deposit_amount' => $appointment->deposit_amount,
            'is_paid' => $appointment->is_deposit_paid,
            'paid_at' => $appointment->deposit_paid_at?->toIso8601String(),
            'payment_method' => $appointment->payment_method,
            'payment_method_label' => $appointment->payment_method
                ? (Appointment::paymentMethods()[$appointment->payment_method] ?? $appointment->payment_method)
                : null,
            'payment_reference' => $appointment->payment_reference,
            'is_refunded' => $appointment->is_refunded,
            'refunded_at' => $appointment->refunded_at?->toIso8601String(),
            'refund_amount' => $appointment->refund_amount,
            'refund_reason' => $appointment->refund_reason,
            'can_refund' => $appointment->canBeRefunded(),
        ];

        // Add Stripe-specific info if applicable
        if ($appointment->is_stripe_payment) {
            $status['stripe_checkout_session_id'] = $appointment->stripe_checkout_session_id;
            $status['stripe_payment_intent_id'] = $appointment->stripe_payment_intent_id;
        }

        return $status;
    }

    /**
     * Calculate the deposit amount based on studio settings.
     *
     * @param Appointment $appointment The appointment
     * @param Studio $studio The studio
     * @return float The deposit amount
     */
    public function calculateDepositAmount(Appointment $appointment, Studio $studio): float
    {
        $depositType = $studio->booking_deposit_type;
        $depositValue = $studio->booking_deposit_amount;

        if ($depositType === 'fixed') {
            return (float) $depositValue;
        }

        // Percentage-based deposit
        $estimatedPrice = $appointment->estimated_price ?? 0;

        if ($estimatedPrice <= 0) {
            // If no estimated price, use a minimum deposit
            return (float) $depositValue; // Treat as fixed amount
        }

        return round($estimatedPrice * ($depositValue / 100), 2);
    }

    /**
     * Build a description for the deposit line item.
     *
     * @param Appointment $appointment The appointment
     * @param Studio $studio The studio
     * @return string The description
     */
    protected function buildDepositDescription(Appointment $appointment, Studio $studio): string
    {
        $parts = [];

        $parts[] = "Deposit for appointment at {$studio->name}";

        if ($appointment->artist) {
            $parts[] = "Artist: {$appointment->artist->display_name}";
        }

        if ($appointment->scheduled_at) {
            $parts[] = "Date: {$appointment->scheduled_at->format('M j, Y g:i A')}";
        }

        return implode(' | ', $parts);
    }

    /**
     * Map a custom reason to a Stripe refund reason.
     *
     * @param string|null $reason The custom reason
     * @return string The Stripe reason
     */
    protected function mapRefundReason(?string $reason): string
    {
        if (!$reason) {
            return 'requested_by_customer';
        }

        $lowerReason = strtolower($reason);

        if (str_contains($lowerReason, 'duplicate')) {
            return 'duplicate';
        }

        if (str_contains($lowerReason, 'fraud')) {
            return 'fraudulent';
        }

        return 'requested_by_customer';
    }

    /**
     * Verify that Stripe is properly configured.
     *
     * @return bool Whether Stripe is configured
     */
    public static function isConfigured(): bool
    {
        return !empty(config('stripe.key')) && !empty(config('stripe.secret'));
    }

    /**
     * Get the Stripe publishable key for frontend use.
     *
     * @return string|null The publishable key
     */
    public static function getPublishableKey(): ?string
    {
        return config('stripe.key');
    }
}
