<?php

namespace App\Filament\Resources\WaiverResource\Pages;

use App\Filament\Resources\WaiverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWaiver extends EditRecord
{
    protected static string $resource = WaiverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
