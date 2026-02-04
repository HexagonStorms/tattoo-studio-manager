<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\WaiverResource;
use App\Models\Waiver;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingActionsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $studio = Filament::getTenant();

        if (! $studio) {
            return [];
        }

        // Count unsigned waivers for current studio
        $unsignedWaivers = Waiver::query()
            ->whereNull('signed_at')
            ->count();

        // Placeholder for pending bookings (when Appointment model exists)
        $pendingBookings = 0;

        // Placeholder for this week's appointments (when Appointment model exists)
        $thisWeekAppointments = 0;

        return [
            Stat::make('Unsigned Waivers', $unsignedWaivers)
                ->description('Awaiting client signature')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($unsignedWaivers > 0 ? 'warning' : 'success')
                ->url(WaiverResource::getUrl('index', [
                    'tenant' => $studio->slug,
                    'tableFilters' => [
                        'unsigned' => ['isActive' => true],
                    ],
                ])),

            Stat::make('Pending Bookings', $pendingBookings)
                ->description('Awaiting confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingBookings > 0 ? 'warning' : 'success'),
                // ->url() // Add URL when Appointment resource exists

            Stat::make("This Week's Appointments", $thisWeekAppointments)
                ->description(Carbon::now()->startOfWeek()->format('M j') . ' - ' . Carbon::now()->endOfWeek()->format('M j'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
                // ->url() // Add URL when Appointment resource exists
        ];
    }

    public static function canView(): bool
    {
        return Filament::getTenant() !== null;
    }
}
