<?php

namespace App\Filament\Resources\ProductMoodleMappingResource\Pages;

use App\Filament\Resources\ProductMoodleMappingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductMoodleMapping extends EditRecord
{
    protected static string $resource = ProductMoodleMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
