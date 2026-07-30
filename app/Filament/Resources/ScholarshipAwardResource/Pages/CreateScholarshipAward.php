<?php

namespace App\Filament\Resources\ScholarshipAwardResource\Pages;

use App\Filament\Resources\ScholarshipAwardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateScholarshipAward extends CreateRecord
{
    protected static string $resource = ScholarshipAwardResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['awarded_by_user_id'] = auth()->id();
        $data['awarded_at'] ??= now();

        return $data;
    }
}
