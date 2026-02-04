<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\WaiverResource;
use App\Models\Appointment;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewAppointment extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('confirm')
                ->label('Confirm Appointment')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === Appointment::STATUS_PENDING)
                ->action(function () {
                    $this->record->confirm();
                    Notification::make()
                        ->title('Appointment confirmed')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('complete')
                ->label('Mark Completed')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn (): bool => in_array($this->record->status, [
                    Appointment::STATUS_PENDING,
                    Appointment::STATUS_CONFIRMED,
                ]))
                ->action(function () {
                    $this->record->complete();
                    Notification::make()
                        ->title('Appointment marked as completed')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('send_reminder')
                ->label('Send Reminder')
                ->icon('heroicon-o-bell')
                ->color('primary')
                ->visible(fn (): bool => in_array($this->record->status, [
                    Appointment::STATUS_PENDING,
                    Appointment::STATUS_CONFIRMED,
                ]) && $this->record->scheduled_at->isFuture())
                ->requiresConfirmation()
                ->modalDescription('Send a reminder email to the client about their upcoming appointment.')
                ->action(function () {
                    // TODO: Implement reminder email
                    Notification::make()
                        ->title('Reminder sent')
                        ->body('Reminder functionality coming soon!')
                        ->info()
                        ->send();
                }),

            Actions\Action::make('view_waiver')
                ->label('View Waiver')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->visible(fn (): bool => $this->record->waiver_id !== null)
                ->url(fn (): string => WaiverResource::getUrl('edit', ['record' => $this->record->waiver_id])),

            Actions\EditAction::make(),

            Actions\Action::make('cancel')
                ->label('Cancel Appointment')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    \Filament\Forms\Components\Textarea::make('cancellation_reason')
                        ->label('Cancellation Reason')
                        ->rows(2),
                ])
                ->visible(fn (): bool => $this->record->canBeCancelled())
                ->action(function (array $data) {
                    $this->record->cancel($data['cancellation_reason'] ?? null);
                    Notification::make()
                        ->title('Appointment cancelled')
                        ->warning()
                        ->send();
                }),
        ];
    }
}
