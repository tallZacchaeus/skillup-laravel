<?php

namespace App\Filament\Resources\ProductVisibilityRuleResource\Pages;

use App\Filament\Resources\ProductVisibilityRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductVisibilityRules extends ListRecords
{
    protected static string $resource = ProductVisibilityRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
