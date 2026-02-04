<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Artist;
use App\Models\Waiver;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudioStatsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $studio = Filament::getTenant();

        if (! $studio) {
            return [];
        }

        // Count unique clients by email from waivers
        $totalClients = Waiver::query()
            ->whereNotNull('client_email')
            ->distinct()
            ->count('client_email');

        // Count waivers created this month
        $waiversThisMonth = Waiver::query()
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Get last month's waiver count for comparison
        $waiversLastMonth = Waiver::query()
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();

        // Calculate trend
        $waiverTrend = $waiversLastMonth > 0
            ? round((($waiversThisMonth - $waiversLastMonth) / $waiversLastMonth) * 100)
            : ($waiversThisMonth > 0 ? 100 : 0);

        // Count active artists
        $activeArtists = Artist::query()
            ->where('is_active', true)
            ->count();

        // Count upcoming appointments
        $upcomingAppointments = Appointment::query()
            ->where('scheduled_at', '>=', now())
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
            ->count();

        // Count appointments this month
        $appointmentsThisMonth = Appointment::query()
            ->whereMonth('scheduled_at', Carbon::now()->month)
            ->whereYear('scheduled_at', Carbon::now()->year)
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED])
            ->count();

        return [
            Stat::make('Total Clients', $totalClients)
                ->description('Unique clients served')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Upcoming Appointments', $upcomingAppointments)
                ->description($appointmentsThisMonth . ' this month')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make('Waivers This Month', $waiversThisMonth)
                ->description($this->getTrendDescription($waiverTrend))
                ->descriptionIcon($waiverTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($waiverTrend >= 0 ? 'success' : 'danger')
                ->chart($this->getWaiverChartData()),

            Stat::make('Active Artists', $activeArtists)
                ->description('Team members')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }

    protected function getTrendDescription(int $trend): string
    {
        if ($trend === 0) {
            return 'Same as last month';
        }

        $direction = $trend > 0 ? 'up' : 'down';
        return abs($trend) . '% ' . $direction . ' from last month';
    }

    /**
     * Get simple chart data showing waivers per day for the last 7 days.
     */
    protected function getWaiverChartData(): array
    {
        $waivers = Waiver::query()
            ->whereBetween('created_at', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $data[] = $waivers->get($date, 0);
        }

        return $data;
    }

    public static function canView(): bool
    {
        return Filament::getTenant() !== null;
    }
}
