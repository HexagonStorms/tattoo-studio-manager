<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class CalendarWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.calendar-widget';

    public int $weekOffset = 0;

    public function previousWeek(): void
    {
        $this->weekOffset--;
    }

    public function nextWeek(): void
    {
        $this->weekOffset++;
    }

    public function today(): void
    {
        $this->weekOffset = 0;
    }

    public function getWeekDays(): array
    {
        $startOfWeek = Carbon::today()->startOfWeek()->addWeeks($this->weekOffset);
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $days[] = [
                'date' => $date,
                'dayName' => $date->format('D'),
                'dayNumber' => $date->format('j'),
                'monthName' => $date->format('M'),
                'isToday' => $date->isToday(),
                'isPast' => $date->isPast() && ! $date->isToday(),
                'appointments' => $this->getAppointmentsForDate($date),
            ];
        }

        return $days;
    }

    public function getAppointmentsForDate(Carbon $date): array
    {
        return Appointment::query()
            ->whereDate('scheduled_at', $date)
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED])
            ->orderBy('scheduled_at')
            ->with('artist')
            ->get()
            ->map(function (Appointment $appointment) {
                return [
                    'id' => $appointment->id,
                    'time' => $appointment->scheduled_at->format('g:i A'),
                    'client_name' => $appointment->client_name,
                    'artist_name' => $appointment->artist?->display_name ?? 'Unassigned',
                    'status' => $appointment->status,
                    'status_label' => Appointment::statuses()[$appointment->status] ?? $appointment->status,
                    'status_color' => match ($appointment->status) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'completed' => 'info',
                        'no_show' => 'gray',
                        default => 'gray',
                    },
                    'url' => AppointmentResource::getUrl('view', ['record' => $appointment->id]),
                ];
            })
            ->toArray();
    }

    public function getWeekRange(): string
    {
        $startOfWeek = Carbon::today()->startOfWeek()->addWeeks($this->weekOffset);
        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        if ($startOfWeek->month === $endOfWeek->month) {
            return $startOfWeek->format('M j') . ' - ' . $endOfWeek->format('j, Y');
        }

        return $startOfWeek->format('M j') . ' - ' . $endOfWeek->format('M j, Y');
    }

    public static function canView(): bool
    {
        return Filament::getTenant() !== null;
    }
}
