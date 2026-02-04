<?php

namespace App\Livewire\Public;

use App\Models\Appointment;
use App\Models\Studio;
use App\Services\TenantService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BookingConfirmation extends Component
{
    public Studio $studio;
    public Appointment $appointment;

    public function mount(TenantService $tenantService, Appointment $appointment): void
    {
        $this->studio = $tenantService->current();
        $this->appointment = $appointment->load(['artist', 'service']);

        // Verify the appointment belongs to the current studio
        if ($this->appointment->studio_id !== $this->studio->id) {
            abort(404);
        }
    }

    #[Computed]
    public function confirmationNumber(): string
    {
        return $this->appointment->payment_reference ?? 'N/A';
    }

    #[Computed]
    public function googleCalendarUrl(): string
    {
        $title = urlencode("Tattoo Appointment at {$this->studio->name}");
        $details = urlencode(
            "Artist: {$this->appointment->artist->display_name}\n" .
            ($this->appointment->service ? "Service: {$this->appointment->service->name}\n" : '') .
            "Confirmation: {$this->confirmationNumber}"
        );
        $location = urlencode($this->studio->address ?? '');
        $startTime = $this->appointment->scheduled_at->format('Ymd\THis');
        $endTime = $this->appointment->ends_at->format('Ymd\THis');

        return "https://calendar.google.com/calendar/render?action=TEMPLATE" .
            "&text={$title}" .
            "&dates={$startTime}/{$endTime}" .
            "&details={$details}" .
            "&location={$location}";
    }

    #[Computed]
    public function outlookCalendarUrl(): string
    {
        $title = urlencode("Tattoo Appointment at {$this->studio->name}");
        $details = urlencode(
            "Artist: {$this->appointment->artist->display_name}\n" .
            ($this->appointment->service ? "Service: {$this->appointment->service->name}\n" : '') .
            "Confirmation: {$this->confirmationNumber}"
        );
        $location = urlencode($this->studio->address ?? '');
        $startTime = $this->appointment->scheduled_at->format('Y-m-d\TH:i:s');
        $endTime = $this->appointment->ends_at->format('Y-m-d\TH:i:s');

        return "https://outlook.live.com/calendar/0/deeplink/compose?subject={$title}" .
            "&startdt={$startTime}" .
            "&enddt={$endTime}" .
            "&body={$details}" .
            "&location={$location}";
    }

    #[Computed]
    public function appleCalendarUrl(): string
    {
        // Generate ICS file content for Apple Calendar
        $title = "Tattoo Appointment at {$this->studio->name}";
        $description = "Artist: {$this->appointment->artist->display_name}\\n" .
            ($this->appointment->service ? "Service: {$this->appointment->service->name}\\n" : '') .
            "Confirmation: {$this->confirmationNumber}";
        $location = $this->studio->address ?? '';

        $startTime = $this->appointment->scheduled_at->format('Ymd\THis\Z');
        $endTime = $this->appointment->ends_at->format('Ymd\THis\Z');

        $ics = "BEGIN:VCALENDAR\n" .
            "VERSION:2.0\n" .
            "BEGIN:VEVENT\n" .
            "DTSTART:{$startTime}\n" .
            "DTEND:{$endTime}\n" .
            "SUMMARY:{$title}\n" .
            "DESCRIPTION:{$description}\n" .
            "LOCATION:{$location}\n" .
            "END:VEVENT\n" .
            "END:VCALENDAR";

        return 'data:text/calendar;charset=utf-8,' . urlencode($ics);
    }

    #[Computed]
    public function hasWaiver(): bool
    {
        return $this->appointment->waiver_id !== null;
    }

    #[Computed]
    public function depositIsPaid(): bool
    {
        return $this->appointment->deposit_paid_at !== null;
    }

    public function render()
    {
        return view('livewire.public.booking-confirmation');
    }
}
