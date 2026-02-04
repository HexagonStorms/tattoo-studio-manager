<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Display the booking wizard or "booking disabled" message.
     */
    public function index(Request $request, ?string $artistSlug = null): View
    {
        $studio = $this->tenantService->current();

        // Check if online booking is enabled
        $bookingEnabled = $studio->booking_enabled ?? false;

        if (!$bookingEnabled) {
            return view('public.booking.disabled', [
                'studio' => $studio,
            ]);
        }

        // Get all artists accepting bookings for reference
        $availableArtists = $studio->artists()
            ->active()
            ->acceptingBookings()
            ->orderBy('sort_order')
            ->get();

        // If no artists available for booking, show disabled message
        if ($availableArtists->isEmpty()) {
            return view('public.booking.disabled', [
                'studio' => $studio,
                'reason' => 'no_artists',
            ]);
        }

        return view('public.booking.index', [
            'studio' => $studio,
            'artistSlug' => $artistSlug,
        ]);
    }

    /**
     * Display the booking confirmation page.
     */
    public function confirmation(Appointment $appointment): View
    {
        $studio = $this->tenantService->current();

        // Validate appointment belongs to current studio
        if ($appointment->studio_id !== $studio->id) {
            abort(404);
        }

        return view('public.booking.confirmation', [
            'studio' => $studio,
            'appointment' => $appointment,
        ]);
    }
}
