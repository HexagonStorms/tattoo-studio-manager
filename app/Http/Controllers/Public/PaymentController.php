<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\PaymentService;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected TenantService $tenantService
    ) {}

    /**
     * Create a Stripe Checkout session and redirect to payment.
     */
    public function checkout(Request $request, Appointment $appointment): RedirectResponse
    {
        $studio = $this->tenantService->current();

        // Verify appointment belongs to current studio
        if ($appointment->studio_id !== $studio->id) {
            abort(404);
        }

        // Don't allow checkout if already paid
        if ($appointment->is_deposit_paid) {
            return redirect()->route('booking.success', ['appointment' => $appointment->id])
                ->with('info', 'Deposit has already been paid.');
        }

        // Don't allow checkout for cancelled appointments
        if ($appointment->status === Appointment::STATUS_CANCELLED) {
            return redirect()->route('home')
                ->with('error', 'This appointment has been cancelled.');
        }

        try {
            $session = $this->paymentService->createDepositCheckout(
                $appointment,
                route('booking.success', ['appointment' => $appointment->id]),
                route('booking.cancel', ['appointment' => $appointment->id])
            );

            return redirect($session->url);
        } catch (ApiErrorException $e) {
            return redirect()->back()
                ->with('error', 'Unable to process payment. Please try again.');
        }
    }

    /**
     * Handle successful payment return from Stripe.
     */
    public function success(Request $request, Appointment $appointment): View
    {
        $studio = $this->tenantService->current();

        // Verify appointment belongs to current studio
        if ($appointment->studio_id !== $studio->id) {
            abort(404);
        }

        // Refresh appointment to get latest payment status
        $appointment->refresh();

        // If we have a session_id, the webhook may not have processed yet
        // In that case, we can process the payment here as a fallback
        $sessionId = $request->query('session_id');

        if ($sessionId && !$appointment->is_deposit_paid) {
            try {
                $this->paymentService->handleSuccessfulPayment($sessionId);
                $appointment->refresh();
            } catch (\Exception $e) {
                // Log but don't fail - the webhook will handle it
                \Log::warning('Failed to process payment on success redirect', [
                    'session_id' => $sessionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $paymentStatus = $this->paymentService->getPaymentStatus($appointment);

        return view('customer.booking.success', [
            'studio' => $studio,
            'appointment' => $appointment,
            'paymentStatus' => $paymentStatus,
        ]);
    }

    /**
     * Handle cancelled payment return from Stripe.
     */
    public function cancel(Request $request, Appointment $appointment): View
    {
        $studio = $this->tenantService->current();

        // Verify appointment belongs to current studio
        if ($appointment->studio_id !== $studio->id) {
            abort(404);
        }

        return view('customer.booking.cancel', [
            'studio' => $studio,
            'appointment' => $appointment,
        ]);
    }
}
