<?php

namespace App\Filament\Resources\DiscountEligibilityListResource\Pages;

use App\Filament\Resources\DiscountEligibilityListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiscountEligibilityLists extends ListRecords
{
    protected static string $resource = DiscountEligibilityListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
