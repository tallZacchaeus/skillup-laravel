<?php

namespace App\Filament\Resources\MoodleConnectionResource\Pages;

use App\Filament\Resources\MoodleConnectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMoodleConnections extends ListRecords
{
    protected static string $resource = MoodleConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
