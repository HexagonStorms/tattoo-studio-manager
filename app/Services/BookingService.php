<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Artist;
use App\Models\ArtistAvailability;
use App\Models\Service;
use App\Models\Studio;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Slot duration in minutes.
     */
    protected const SLOT_DURATION = 30;

    /**
     * Get available time slots for an artist on a specific date.
     */
    public function getAvailableSlots(Artist $artist, Carbon $date, int $durationMinutes): array
    {
        $studio = $artist->studio;
        $dayOfWeek = $date->dayOfWeek;

        // Get artist availability for this day of week
        $availability = ArtistAvailability::where('artist_id', $artist->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->first();

        if (!$availability) {
            return [];
        }

        // Parse working hours
        $startTime = Carbon::parse($availability->start_time)->setDateFrom($date);
        $endTime = Carbon::parse($availability->end_time)->setDateFrom($date);

        // Get existing appointments for this artist on this date
        $existingAppointments = Appointment::where('artist_id', $artist->id)
            ->whereDate('scheduled_at', $date)
            ->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED,
            ])
            ->get();

        // Generate available slots
        $slots = [];
        $currentSlot = $startTime->copy();
        $minimumNoticeTime = $this->getMinimumNoticeTime($studio);

        while ($currentSlot->copy()->addMinutes($durationMinutes)->lte($endTime)) {
            // Check if slot is in the past or within minimum notice period
            if ($currentSlot->lte($minimumNoticeTime)) {
                $currentSlot->addMinutes(self::SLOT_DURATION);
                continue;
            }

            // Check if slot conflicts with existing appointments
            $slotEnd = $currentSlot->copy()->addMinutes($durationMinutes);
            $isAvailable = true;

            foreach ($existingAppointments as $appointment) {
                $appointmentStart = $appointment->scheduled_at;
                $appointmentEnd = $appointment->scheduled_at->copy()->addMinutes($appointment->duration_minutes);

                // Check for overlap
                if ($currentSlot < $appointmentEnd && $slotEnd > $appointmentStart) {
                    $isAvailable = false;
                    break;
                }
            }

            $slots[] = [
                'time' => $currentSlot->format('H:i'),
                'display' => $currentSlot->format('g:i A'),
                'available' => $isAvailable,
                'datetime' => $currentSlot->toIso8601String(),
            ];

            $currentSlot->addMinutes(self::SLOT_DURATION);
        }

        return $slots;
    }

    /**
     * Get the minimum notice time based on studio settings.
     */
    protected function getMinimumNoticeTime(Studio $studio): Carbon
    {
        $minimumNoticeHours = $studio->booking_minimum_notice_hours ?? 24;
        return now()->addHours($minimumNoticeHours);
    }

    /**
     * Check if a specific slot is available.
     */
    public function isSlotAvailable(Artist $artist, Carbon $datetime, int $durationMinutes): bool
    {
        $studio = $artist->studio;
        $minimumNoticeTime = $this->getMinimumNoticeTime($studio);

        // Check if slot is too soon
        if ($datetime->lte($minimumNoticeTime)) {
            return false;
        }

        // Check artist availability for this day
        $dayOfWeek = $datetime->dayOfWeek;
        $availability = ArtistAvailability::where('artist_id', $artist->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->first();

        if (!$availability) {
            return false;
        }

        // Check if time is within working hours
        $slotTime = $datetime->format('H:i:s');
        $startTime = Carbon::parse($availability->start_time)->format('H:i:s');
        $endTime = Carbon::parse($availability->end_time)->format('H:i:s');

        if ($slotTime < $startTime || $datetime->copy()->addMinutes($durationMinutes)->format('H:i:s') > $endTime) {
            return false;
        }

        // Check for conflicts with existing appointments
        $slotEnd = $datetime->copy()->addMinutes($durationMinutes);

        $conflictingAppointments = Appointment::where('artist_id', $artist->id)
            ->whereDate('scheduled_at', $datetime)
            ->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED,
            ])
            ->get();

        foreach ($conflictingAppointments as $appointment) {
            $appointmentStart = $appointment->scheduled_at;
            $appointmentEnd = $appointment->scheduled_at->copy()->addMinutes($appointment->duration_minutes);

            // Check for overlap
            if ($datetime < $appointmentEnd && $slotEnd > $appointmentStart) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate the deposit amount for a booking.
     */
    public function calculateDeposit(Studio $studio, ?Service $service): float
    {
        $depositType = $studio->booking_deposit_type ?? 'percentage';
        $depositAmount = $studio->booking_deposit_amount ?? 0;

        if ($depositType === 'fixed') {
            return (float) $depositAmount;
        }

        // Percentage-based deposit
        if ($service && $service->price && $service->price_type === 'fixed') {
            return round(($service->price * $depositAmount) / 100, 2);
        }

        // Default minimum deposit if no service price available
        return (float) $depositAmount;
    }

    /**
     * Create a new booking (appointment).
     * Uses database transaction with locking to prevent double-booking.
     */
    public function createBooking(array $data): Appointment
    {
        $studio = Studio::findOrFail($data['studio_id']);
        $artist = Artist::findOrFail($data['artist_id']);
        $service = isset($data['service_id']) ? Service::find($data['service_id']) : null;

        $scheduledAt = Carbon::parse($data['scheduled_at']);
        $duration = $service?->duration_minutes ?? ($data['duration_minutes'] ?? 60);

        return DB::transaction(function () use ($studio, $artist, $service, $scheduledAt, $duration, $data) {
            // Lock existing appointments for this artist on this date to prevent race conditions
            Appointment::where('artist_id', $artist->id)
                ->whereDate('scheduled_at', $scheduledAt)
                ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
                ->lockForUpdate()
                ->get();

            // Re-validate slot availability inside transaction
            if (!$this->isSlotAvailable($artist, $scheduledAt, $duration)) {
                throw new \Exception('The selected time slot is no longer available.');
            }

            // Calculate deposit
            $depositAmount = $this->calculateDeposit($studio, $service);

            // Generate confirmation number
            $confirmationNumber = 'BK-' . strtoupper(Str::random(8));

            // Create appointment
            return Appointment::create([
                'studio_id' => $studio->id,
                'artist_id' => $artist->id,
                'service_id' => $service?->id,
                'client_name' => $data['client_name'],
                'client_email' => $data['client_email'],
                'client_phone' => $data['client_phone'],
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => $duration,
                'status' => Appointment::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
                'tattoo_description' => $data['tattoo_description'] ?? null,
                'tattoo_placement' => $data['tattoo_placement'] ?? null,
                'estimated_price' => $service?->price,
                'deposit_amount' => $depositAmount,
                'payment_reference' => $confirmationNumber,
            ]);
        });
    }

    /**
     * Get artists available for booking at a studio.
     */
    public function getAvailableArtists(Studio $studio): Collection
    {
        return $studio->artists()
            ->active()
            ->acceptingBookings()
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get services available for booking at a studio.
     */
    public function getAvailableServices(Studio $studio): Collection
    {
        return Service::where('studio_id', $studio->id)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get artist working hours for a specific date.
     */
    public function getArtistWorkingHours(Artist $artist, Carbon $date): ?array
    {
        $dayOfWeek = $date->dayOfWeek;

        $availability = ArtistAvailability::where('artist_id', $artist->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->first();

        if (!$availability) {
            return null;
        }

        return [
            'start' => Carbon::parse($availability->start_time)->format('g:i A'),
            'end' => Carbon::parse($availability->end_time)->format('g:i A'),
        ];
    }

    /**
     * Get earliest bookable date for a studio.
     */
    public function getEarliestBookableDate(Studio $studio): Carbon
    {
        $minimumNoticeHours = $studio->booking_minimum_notice_hours ?? 24;
        return now()->addHours($minimumNoticeHours)->startOfDay()->addDay();
    }

    /**
     * Find the first available artist for a given date/time.
     */
    public function findFirstAvailableArtist(Studio $studio, Carbon $datetime, int $durationMinutes): ?Artist
    {
        $artists = $this->getAvailableArtists($studio);

        foreach ($artists as $artist) {
            if ($this->isSlotAvailable($artist, $datetime, $durationMinutes)) {
                return $artist;
            }
        }

        return null;
    }

    /**
     * Get disabled dates for calendar (dates with no availability).
     */
    public function getDisabledDates(Artist $artist, Carbon $startDate, Carbon $endDate): array
    {
        $disabledDates = [];
        $current = $startDate->copy();

        // Get artist's available days of week
        $availableDays = ArtistAvailability::where('artist_id', $artist->id)
            ->where('is_available', true)
            ->pluck('day_of_week')
            ->toArray();

        while ($current->lte($endDate)) {
            // Disable if artist doesn't work this day
            if (!in_array($current->dayOfWeek, $availableDays)) {
                $disabledDates[] = $current->format('Y-m-d');
            }

            $current->addDay();
        }

        return $disabledDates;
    }
}
