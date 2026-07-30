<?php

namespace App\Filament\Resources\DiscountEligibleEmailResource\Pages;

use App\Filament\Resources\DiscountEligibleEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDiscountEligibleEmail extends EditRecord
{
    protected static string $resource = DiscountEligibleEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
