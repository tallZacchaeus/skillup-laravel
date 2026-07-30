<?php

namespace App\Filament\Resources\ProductPaymentPlanResource\Pages;

use App\Filament\Resources\ProductPaymentPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductPaymentPlans extends ListRecords
{
    protected static string $resource = ProductPaymentPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
