<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Handle incoming Stripe webhook events.
     */
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('stripe.webhook_secret');

        // Verify webhook signature
        if ($webhookSecret) {
            try {
                // Set webhook tolerance before verification
                Webhook::setTolerance(config('stripe.webhook_tolerance', 300));

                $event = Webhook::constructEvent(
                    $payload,
                    $sigHeader,
                    $webhookSecret
                );
            } catch (SignatureVerificationException $e) {
                Log::warning('Stripe webhook signature verification failed', [
                    'error' => $e->getMessage(),
                ]);
                return response('Invalid signature', 400);
            } catch (\UnexpectedValueException $e) {
                Log::warning('Invalid Stripe webhook payload', [
                    'error' => $e->getMessage(),
                ]);
                return response('Invalid payload', 400);
            }
        } else {
            // No webhook secret configured - parse payload directly (development only)
            try {
                $event = \Stripe\Event::constructFrom(
                    json_decode($payload, true)
                );
            } catch (\Exception $e) {
                Log::warning('Failed to parse Stripe webhook payload', [
                    'error' => $e->getMessage(),
                ]);
                return response('Invalid payload', 400);
            }

            if (!app()->environment('local', 'testing')) {
                Log::error('Stripe webhook received without signature verification in production');
                return response('Webhook secret not configured', 500);
            }
        }

        Log::info('Stripe webhook received', [
            'type' => $event->type,
            'id' => $event->id,
        ]);

        // Route to appropriate handler
        return match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutSessionCompleted($event),
            'charge.refunded' => $this->handleChargeRefunded($event),
            'payment_intent.succeeded' => $this->handlePaymentIntentSucceeded($event),
            'payment_intent.payment_failed' => $this->handlePaymentIntentFailed($event),
            default => $this->handleUnknownEvent($event),
        };
    }

    /**
     * Handle checkout.session.completed event.
     */
    protected function handleCheckoutSessionCompleted(\Stripe\Event $event): Response
    {
        $session = $event->data->object;

        Log::info('Processing checkout.session.completed', [
            'session_id' => $session->id,
            'payment_status' => $session->payment_status,
        ]);

        // Only process if payment was successful
        if ($session->payment_status !== 'paid') {
            Log::info('Checkout session not paid, skipping', [
                'session_id' => $session->id,
                'status' => $session->payment_status,
            ]);
            return response('OK', 200);
        }

        try {
            $appointment = $this->paymentService->handleSuccessfulPayment($session->id);

            if ($appointment) {
                Log::info('Checkout session payment processed', [
                    'session_id' => $session->id,
                    'appointment_id' => $appointment->id,
                ]);
            } else {
                Log::warning('Could not find appointment for checkout session', [
                    'session_id' => $session->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing checkout session', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Return 200 to prevent Stripe from retrying
            // The error is logged and can be manually resolved
        }

        return response('OK', 200);
    }

    /**
     * Handle charge.refunded event.
     */
    protected function handleChargeRefunded(\Stripe\Event $event): Response
    {
        $charge = $event->data->object;

        Log::info('Processing charge.refunded', [
            'charge_id' => $charge->id,
            'payment_intent' => $charge->payment_intent,
            'amount_refunded' => $charge->amount_refunded,
        ]);

        if (!$charge->payment_intent) {
            Log::warning('Charge refund has no payment_intent', [
                'charge_id' => $charge->id,
            ]);
            return response('OK', 200);
        }

        try {
            $appointment = $this->paymentService->handleRefundEvent(
                $charge->payment_intent,
                $charge->amount_refunded
            );

            if ($appointment) {
                Log::info('Refund processed', [
                    'charge_id' => $charge->id,
                    'appointment_id' => $appointment->id,
                ]);
            } else {
                Log::warning('Could not find appointment for refund', [
                    'charge_id' => $charge->id,
                    'payment_intent' => $charge->payment_intent,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing refund', [
                'charge_id' => $charge->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response('OK', 200);
    }

    /**
     * Handle payment_intent.succeeded event.
     */
    protected function handlePaymentIntentSucceeded(\Stripe\Event $event): Response
    {
        $paymentIntent = $event->data->object;

        Log::info('Payment intent succeeded', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
        ]);

        // We primarily handle this via checkout.session.completed
        // This is logged for debugging purposes

        return response('OK', 200);
    }

    /**
     * Handle payment_intent.payment_failed event.
     */
    protected function handlePaymentIntentFailed(\Stripe\Event $event): Response
    {
        $paymentIntent = $event->data->object;

        Log::warning('Payment intent failed', [
            'payment_intent_id' => $paymentIntent->id,
            'last_payment_error' => $paymentIntent->last_payment_error,
        ]);

        // Could implement notification to studio here
        // For now, just log it

        return response('OK', 200);
    }

    /**
     * Handle unknown/unhandled events.
     */
    protected function handleUnknownEvent(\Stripe\Event $event): Response
    {
        Log::debug('Unhandled Stripe webhook event', [
            'type' => $event->type,
            'id' => $event->id,
        ]);

        return response('OK', 200);
    }
}
