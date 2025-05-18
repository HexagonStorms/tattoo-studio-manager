<?php

namespace App\Filament\Resources\WaiverResource\Pages;

use App\Filament\Resources\WaiverResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWaivers extends ListRecords
{
    protected static string $resource = WaiverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
