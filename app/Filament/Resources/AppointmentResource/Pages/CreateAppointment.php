<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
