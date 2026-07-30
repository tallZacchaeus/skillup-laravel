<?php

namespace App\Filament\Resources\ScholarshipApplicationResource\Pages;

use App\Filament\Resources\ScholarshipApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScholarshipApplications extends ListRecords
{
    protected static string $resource = ScholarshipApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
