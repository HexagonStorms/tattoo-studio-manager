<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CalendarWidget;
use App\Filament\Widgets\PendingActionsWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentWaiversWidget;
use App\Filament\Widgets\StudioStatsWidget;
use App\Filament\Widgets\TodaysAppointmentsWidget;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard';

    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            // Row 1: Today's appointments (full width)
            TodaysAppointmentsWidget::class,

            // Row 2: Weekly calendar (full width)
            CalendarWidget::class,

            // Row 3: Pending actions stats (full width)
            PendingActionsWidget::class,

            // Row 4: Quick actions (left) + Recent waivers (right)
            QuickActionsWidget::class,
            RecentWaiversWidget::class,

            // Row 5: Studio stats (full width)
            StudioStatsWidget::class,
        ];
    }

    public function getVisibleWidgets(): array
    {
        return collect($this->getWidgets())
            ->filter(fn (string $widget): bool => $widget::canView())
            ->toArray();
    }

    public function getHeaderWidgets(): array
    {
        return [];
    }

    public function getFooterWidgets(): array
    {
        return [];
    }

    /**
     * Custom greeting based on time of day.
     */
    public function getHeading(): string
    {
        $hour = now()->hour;
        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        $studio = Filament::getTenant();
        $studioName = $studio?->name ?? 'Studio';

        return "{$greeting}, {$studioName}";
    }

    public function getSubheading(): ?string
    {
        return "Here's what's happening today.";
    }
}
