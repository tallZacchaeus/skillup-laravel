<?php

namespace App\Filament\Resources\ScholarshipAwardResource\Pages;

use App\Filament\Resources\ScholarshipAwardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScholarshipAward extends EditRecord
{
    protected static string $resource = ScholarshipAwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
