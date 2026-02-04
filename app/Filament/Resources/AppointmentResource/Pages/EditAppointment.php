<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Split scheduled_at into scheduled_date and scheduled_time for the form
        if (isset($data['scheduled_at'])) {
            $scheduledAt = Carbon::parse($data['scheduled_at']);
            $data['scheduled_date'] = $scheduledAt->toDateString();
            $data['scheduled_time'] = $scheduledAt->format('H:i');
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Combine scheduled_date and scheduled_time into scheduled_at
        if (isset($data['scheduled_date']) && isset($data['scheduled_time'])) {
            $date = Carbon::parse($data['scheduled_date']);
            $time = Carbon::parse($data['scheduled_time']);

            $data['scheduled_at'] = $date->setTime($time->hour, $time->minute, 0);

            unset($data['scheduled_date'], $data['scheduled_time']);
        }

        return $data;
    }
}
