<?php

namespace App\Livewire\Public;

use App\Models\Artist;
use App\Services\BookingService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TimeSlotPicker extends Component
{
    public ?int $artistId = null;
    public int $serviceDuration = 60;
    public int $minNoticeHours = 24;
    public ?string $selectedDate = null;
    public ?string $selectedTime = null;
    public ?string $selectedDatetime = null;

    protected $listeners = ['artistChanged' => 'handleArtistChanged'];

    public function handleArtistChanged(?int $artistId): void
    {
        $this->artistId = $artistId;
        $this->selectedDate = null;
        $this->selectedTime = null;
        $this->selectedDatetime = null;
    }

    #[Computed]
    public function artist()
    {
        if (!$this->artistId) {
            return null;
        }

        return Artist::with('studio')->find($this->artistId);
    }

    #[Computed]
    public function minimumDate(): string
    {
        return now()->addHours($this->minNoticeHours)->startOfDay()->addDay()->format('Y-m-d');
    }

    #[Computed]
    public function maximumDate(): string
    {
        // Allow booking up to 3 months in advance
        return now()->addMonths(3)->format('Y-m-d');
    }

    #[Computed]
    public function timeSlots(): array
    {
        if (!$this->artist || !$this->selectedDate) {
            return [];
        }

        $date = Carbon::parse($this->selectedDate);

        return app(BookingService::class)->getAvailableSlots(
            $this->artist,
            $date,
            $this->serviceDuration
        );
    }

    #[Computed]
    public function workingHours(): ?array
    {
        if (!$this->artist || !$this->selectedDate) {
            return null;
        }

        return app(BookingService::class)->getArtistWorkingHours(
            $this->artist,
            Carbon::parse($this->selectedDate)
        );
    }

    #[Computed]
    public function disabledDates(): array
    {
        if (!$this->artist) {
            return [];
        }

        $startDate = Carbon::parse($this->minimumDate);
        $endDate = Carbon::parse($this->maximumDate);

        return app(BookingService::class)->getDisabledDates(
            $this->artist,
            $startDate,
            $endDate
        );
    }

    #[Computed]
    public function calendarDays(): array
    {
        $startDate = Carbon::parse($this->minimumDate)->startOfMonth();
        $endDate = Carbon::parse($this->minimumDate)->endOfMonth();
        $today = now()->startOfDay();

        $days = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dateStr = $current->format('Y-m-d');
            $isPast = $current->lt($today);
            $isTooSoon = $current->lt(Carbon::parse($this->minimumDate));
            $isDisabled = in_array($dateStr, $this->disabledDates);

            $days[] = [
                'date' => $dateStr,
                'day' => $current->day,
                'dayOfWeek' => $current->dayOfWeek,
                'isCurrentMonth' => true,
                'isPast' => $isPast,
                'isTooSoon' => $isTooSoon,
                'isDisabled' => $isDisabled || $isPast || $isTooSoon,
                'isSelected' => $this->selectedDate === $dateStr,
                'isToday' => $current->isToday(),
            ];

            $current->addDay();
        }

        return $days;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->selectedTime = null;
        $this->selectedDatetime = null;

        $this->dispatch('dateSelected', date: $date);
    }

    public function selectTime(string $time, string $datetime): void
    {
        $this->selectedTime = $time;
        $this->selectedDatetime = $datetime;

        $this->dispatch('slot-selected', datetime: $datetime, time: $time, date: $this->selectedDate);
    }

    public function render()
    {
        return view('livewire.public.time-slot-picker');
    }
}
