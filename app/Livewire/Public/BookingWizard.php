<?php

namespace App\Livewire\Public;

use App\Models\Artist;
use App\Models\Service;
use App\Models\Studio;
use App\Services\BookingService;
use App\Services\TenantService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class BookingWizard extends Component
{
    public Studio $studio;

    // Current step (1-5)
    public int $currentStep = 1;

    // Step 1: Artist selection
    #[Url(as: 'artist')]
    public ?string $selectedArtistSlug = null;
    public ?int $selectedArtistId = null;
    public bool $anyArtist = false;

    // Step 2: Service selection
    #[Url(as: 'service')]
    public ?string $selectedServiceSlug = null;
    public ?int $selectedServiceId = null;

    // Step 3: Date & Time selection
    public ?string $selectedDate = null;
    public ?string $selectedTime = null;
    public ?string $selectedDatetime = null;

    // Step 4: Client details
    public string $clientName = '';
    public string $clientEmail = '';
    public string $clientPhone = '';
    public string $tattooDescription = '';
    public string $tattooPlacement = '';
    public string $notes = '';

    // Step 5: Review
    public bool $termsAccepted = false;

    // Loading states
    public bool $isSubmitting = false;

    // Error messages
    public ?string $errorMessage = null;

    protected BookingService $bookingService;

    protected $rules = [
        'clientName' => 'required|string|max:255',
        'clientEmail' => 'required|email|max:255',
        'clientPhone' => 'required|string|max:30',
        'tattooDescription' => 'nullable|string|max:2000',
        'tattooPlacement' => 'nullable|string|max:255',
        'notes' => 'nullable|string|max:1000',
        'termsAccepted' => 'accepted',
    ];

    protected $messages = [
        'clientName.required' => 'Please enter your name.',
        'clientEmail.required' => 'Please enter your email address.',
        'clientEmail.email' => 'Please enter a valid email address.',
        'clientPhone.required' => 'Please enter your phone number.',
        'termsAccepted.accepted' => 'You must accept the terms and conditions.',
    ];

    public function boot(BookingService $bookingService): void
    {
        $this->bookingService = $bookingService;
    }

    public function mount(TenantService $tenantService, ?string $artistSlug = null): void
    {
        $this->studio = $tenantService->current();

        // Handle URL parameter for artist
        if ($artistSlug) {
            $this->selectedArtistSlug = $artistSlug;
        }

        // Pre-select artist if specified
        if ($this->selectedArtistSlug) {
            $artist = $this->studio->artists()
                ->where('slug', $this->selectedArtistSlug)
                ->active()
                ->acceptingBookings()
                ->first();

            if ($artist) {
                $this->selectedArtistId = $artist->id;
            }
        }

        // Pre-select service if specified
        if ($this->selectedServiceSlug) {
            $service = Service::where('studio_id', $this->studio->id)
                ->where('slug', $this->selectedServiceSlug)
                ->active()
                ->first();

            if ($service) {
                $this->selectedServiceId = $service->id;
            }
        }
    }

    #[Computed]
    public function artists()
    {
        return $this->studio->artists()
            ->active()
            ->acceptingBookings()
            ->with('portfolioImages')
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function services()
    {
        return Service::where('studio_id', $this->studio->id)
            ->active()
            ->ordered()
            ->get();
    }

    #[Computed]
    public function selectedArtist()
    {
        if (!$this->selectedArtistId) {
            return null;
        }

        return Artist::find($this->selectedArtistId);
    }

    #[Computed]
    public function selectedService()
    {
        if (!$this->selectedServiceId) {
            return null;
        }

        return Service::find($this->selectedServiceId);
    }

    #[Computed]
    public function duration(): int
    {
        return $this->selectedService?->duration_minutes ?? 60;
    }

    #[Computed]
    public function depositAmount(): float
    {
        return app(BookingService::class)->calculateDeposit(
            $this->studio,
            $this->selectedService
        );
    }

    #[Computed]
    public function minimumDate(): string
    {
        return app(BookingService::class)
            ->getEarliestBookableDate($this->studio)
            ->format('Y-m-d');
    }

    #[Computed]
    public function timeSlots(): array
    {
        if (!$this->selectedArtistId || !$this->selectedDate) {
            return [];
        }

        $artist = $this->selectedArtist;
        if (!$artist) {
            return [];
        }

        $date = Carbon::parse($this->selectedDate);

        return app(BookingService::class)->getAvailableSlots(
            $artist,
            $date,
            $this->duration
        );
    }

    #[Computed]
    public function workingHours(): ?array
    {
        if (!$this->selectedArtist || !$this->selectedDate) {
            return null;
        }

        return app(BookingService::class)->getArtistWorkingHours(
            $this->selectedArtist,
            Carbon::parse($this->selectedDate)
        );
    }

    public function selectArtist(int $artistId): void
    {
        $this->selectedArtistId = $artistId;
        $this->anyArtist = false;

        // Reset date/time when artist changes
        $this->selectedDate = null;
        $this->selectedTime = null;
        $this->selectedDatetime = null;
    }

    public function selectAnyArtist(): void
    {
        $this->anyArtist = true;
        $this->selectedArtistId = null;

        // Reset date/time
        $this->selectedDate = null;
        $this->selectedTime = null;
        $this->selectedDatetime = null;
    }

    public function selectService(int $serviceId): void
    {
        $this->selectedServiceId = $serviceId;

        // Reset time when service changes (duration may be different)
        $this->selectedTime = null;
        $this->selectedDatetime = null;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->selectedTime = null;
        $this->selectedDatetime = null;
    }

    public function selectTime(string $time, string $datetime): void
    {
        $this->selectedTime = $time;
        $this->selectedDatetime = $datetime;
    }

    public function nextStep(): void
    {
        $this->errorMessage = null;

        // Validate current step before proceeding
        if (!$this->validateStep($this->currentStep)) {
            return;
        }

        if ($this->currentStep < 5) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        $this->errorMessage = null;

        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        // Only allow going to completed steps or the current step
        if ($step <= $this->currentStep && $step >= 1) {
            $this->currentStep = $step;
        }
    }

    protected function validateStep(int $step): bool
    {
        switch ($step) {
            case 1:
                if (!$this->selectedArtistId && !$this->anyArtist) {
                    $this->errorMessage = 'Please select an artist or choose "Any available artist".';
                    return false;
                }
                return true;

            case 2:
                // Service is optional but if step 2, we should have artists
                return true;

            case 3:
                if (!$this->selectedDate || !$this->selectedTime) {
                    $this->errorMessage = 'Please select a date and time for your appointment.';
                    return false;
                }
                return true;

            case 4:
                $this->validate([
                    'clientName' => 'required|string|max:255',
                    'clientEmail' => 'required|email|max:255',
                    'clientPhone' => 'required|string|max:30',
                ]);
                return true;

            case 5:
                return true;

            default:
                return true;
        }
    }

    public function submitBooking(): void
    {
        $this->errorMessage = null;
        $this->isSubmitting = true;

        try {
            // Final validation
            $this->validate();

            // Handle "any artist" selection
            $artistId = $this->selectedArtistId;
            if ($this->anyArtist && $this->selectedDatetime) {
                $artist = app(BookingService::class)->findFirstAvailableArtist(
                    $this->studio,
                    Carbon::parse($this->selectedDatetime),
                    $this->duration
                );

                if (!$artist) {
                    throw new \Exception('No artists are available at the selected time.');
                }

                $artistId = $artist->id;
            }

            // Create the booking
            $appointment = app(BookingService::class)->createBooking([
                'studio_id' => $this->studio->id,
                'artist_id' => $artistId,
                'service_id' => $this->selectedServiceId,
                'scheduled_at' => $this->selectedDatetime,
                'client_name' => $this->clientName,
                'client_email' => $this->clientEmail,
                'client_phone' => $this->clientPhone,
                'tattoo_description' => $this->tattooDescription,
                'tattoo_placement' => $this->tattooPlacement,
                'notes' => $this->notes,
            ]);

            // Redirect to confirmation page
            $this->redirect(route('booking.confirmation', $appointment->id));

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->isSubmitting = false;
        }
    }

    public function render()
    {
        return view('livewire.public.booking-wizard');
    }
}
