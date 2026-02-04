<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\WaiverResource;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected static string $view = 'filament.widgets.quick-actions-widget';

    public function getActions(): array
    {
        $studio = Filament::getTenant();

        return [
            [
                'label' => 'New Walk-in Waiver',
                'description' => 'Start a waiver for a walk-in client',
                'icon' => 'heroicon-o-document-plus',
                'color' => 'primary',
                'url' => $studio ? WaiverResource::getUrl('create', ['tenant' => $studio->slug]) : '#',
                'enabled' => true,
            ],
            [
                'label' => 'New Appointment',
                'description' => 'Book a new appointment',
                'icon' => 'heroicon-o-calendar-days',
                'color' => 'primary',
                'url' => $studio ? AppointmentResource::getUrl('create', ['tenant' => $studio->slug]) : '#',
                'enabled' => true,
            ],
            [
                'label' => 'Block Time Off',
                'description' => 'Block calendar time for breaks or days off',
                'icon' => 'heroicon-o-clock',
                'color' => 'gray',
                'url' => null,
                'enabled' => false,
                'comingSoon' => true,
            ],
        ];
    }

    public function showComingSoon(): void
    {
        Notification::make()
            ->title('Coming Soon')
            ->body('This feature is currently in development.')
            ->info()
            ->send();
    }

    public static function canView(): bool
    {
        return Filament::getTenant() !== null;
    }
}
