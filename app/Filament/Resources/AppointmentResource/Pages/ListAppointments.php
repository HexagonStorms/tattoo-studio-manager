<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'upcoming' => Tab::make('Upcoming')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('scheduled_at', '>=', now())
                    ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
                    ->orderBy('scheduled_at')
                )
                ->badge(fn () => Appointment::query()
                    ->where('scheduled_at', '>=', now())
                    ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
                    ->count()
                )
                ->badgeColor('primary'),

            'today' => Tab::make('Today')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereDate('scheduled_at', today())
                    ->orderBy('scheduled_at')
                )
                ->badge(fn () => Appointment::query()
                    ->whereDate('scheduled_at', today())
                    ->count()
                )
                ->badgeColor('success'),

            'past' => Tab::make('Past')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('scheduled_at', '<', now())
                    ->orderByDesc('scheduled_at')
                ),

            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', Appointment::STATUS_CANCELLED)
                    ->orderByDesc('cancelled_at')
                )
                ->badge(fn () => Appointment::query()
                    ->where('status', Appointment::STATUS_CANCELLED)
                    ->whereDate('scheduled_at', '>=', now()->subDays(30))
                    ->count()
                )
                ->badgeColor('danger'),

            'all' => Tab::make('All')
                ->modifyQueryUsing(fn (Builder $query) => $query->orderByDesc('scheduled_at')),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'upcoming';
    }
}
