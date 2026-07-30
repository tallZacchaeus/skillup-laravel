<?php

namespace App\Filament\Resources\ProductMoodleMappingResource\Pages;

use App\Filament\Resources\ProductMoodleMappingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductMoodleMappings extends ListRecords
{
    protected static string $resource = ProductMoodleMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
