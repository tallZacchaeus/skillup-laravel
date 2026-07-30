<?php

namespace App\Filament\Resources\ProductPaymentPlanResource\Pages;

use App\Filament\Resources\ProductPaymentPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductPaymentPlan extends EditRecord
{
    protected static string $resource = ProductPaymentPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
