<?php

namespace App\Filament\Resources\ProgramEditionResource\Pages;

use App\Filament\Resources\ProgramEditionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProgramEditions extends ListRecords
{
    protected static string $resource = ProgramEditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
