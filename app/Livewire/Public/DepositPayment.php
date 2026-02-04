<?php

namespace App\Livewire\Public;

use App\Models\Appointment;
use App\Models\Studio;
use App\Services\PaymentService;
use App\Services\TenantService;
use Livewire\Component;

class DepositPayment extends Component
{
    public Appointment $appointment;
    public Studio $studio;
    public float $depositAmount = 0;
    public bool $isPaid = false;
    public bool $isProcessing = false;
    public ?string $errorMessage = null;

    protected PaymentService $paymentService;

    public function boot(PaymentService $paymentService, TenantService $tenantService): void
    {
        $this->paymentService = $paymentService;
        $this->studio = $tenantService->current();
    }

    public function mount(Appointment $appointment): void
    {
        $this->appointment = $appointment;
        $this->isPaid = $appointment->is_deposit_paid;
        $this->depositAmount = $this->paymentService->calculateDepositAmount($appointment, $this->studio);
    }

    /**
     * Redirect to Stripe Checkout for card payment.
     */
    public function payWithCard(): mixed
    {
        if ($this->isPaid) {
            $this->errorMessage = 'Deposit has already been paid.';
            return null;
        }

        if (!PaymentService::isConfigured()) {
            $this->errorMessage = 'Online payments are not configured. Please contact the studio.';
            return null;
        }

        $this->isProcessing = true;
        $this->errorMessage = null;

        try {
            $session = $this->paymentService->createDepositCheckout(
                $this->appointment,
                route('booking.success', ['appointment' => $this->appointment->id]),
                route('booking.cancel', ['appointment' => $this->appointment->id])
            );

            return redirect($session->url);
        } catch (\Exception $e) {
            $this->isProcessing = false;
            $this->errorMessage = 'Unable to process payment. Please try again or contact the studio.';
            return null;
        }
    }

    /**
     * Handle "Pay Later" option - just proceed without payment.
     */
    public function payLater(): mixed
    {
        // Redirect to confirmation page without payment
        return redirect()->route('booking.confirmation', ['appointment' => $this->appointment->id]);
    }

    /**
     * Get the formatted deposit amount.
     */
    public function getFormattedAmountProperty(): string
    {
        return '$' . number_format($this->depositAmount, 2);
    }

    /**
     * Check if Stripe is configured.
     */
    public function getStripeConfiguredProperty(): bool
    {
        return PaymentService::isConfigured();
    }

    /**
     * Get the deposit type description.
     */
    public function getDepositDescriptionProperty(): string
    {
        $type = $this->studio->booking_deposit_type;
        $amount = $this->studio->booking_deposit_amount;

        if ($type === 'fixed') {
            return 'Fixed deposit of $' . number_format($amount, 2);
        }

        return $amount . '% of estimated price';
    }

    public function render()
    {
        return view('livewire.public.deposit-payment');
    }
}
