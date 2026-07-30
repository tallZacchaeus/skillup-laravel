<?php

namespace App\Filament\Resources\ProductMediaResource\Pages;

use App\Filament\Resources\ProductMediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductMedia extends ListRecords
{
    protected static string $resource = ProductMediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
