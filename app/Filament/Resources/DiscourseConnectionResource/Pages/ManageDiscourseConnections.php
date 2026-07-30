<?php

namespace App\Filament\Resources\DiscourseConnectionResource\Pages;

use App\Filament\Resources\DiscourseConnectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDiscourseConnections extends ManageRecords
{
    protected static string $resource = DiscourseConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
