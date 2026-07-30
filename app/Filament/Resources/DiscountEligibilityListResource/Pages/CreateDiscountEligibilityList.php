<?php

namespace App\Filament\Resources\DiscountEligibilityListResource\Pages;

use App\Filament\Resources\DiscountEligibilityListResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscountEligibilityList extends CreateRecord
{
    protected static string $resource = DiscountEligibilityListResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by_user_id'] = auth()->id();

        return $data;
    }
}
