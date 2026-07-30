<?php

namespace App\Filament\Resources\EventRecordingResource\Pages;

use App\Filament\Resources\EventRecordingResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageEventRecordings extends ManageRecords
{
    protected static string $resource = EventRecordingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
