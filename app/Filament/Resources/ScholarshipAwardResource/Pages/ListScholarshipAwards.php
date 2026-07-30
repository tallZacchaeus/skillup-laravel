<?php

namespace App\Filament\Resources\ScholarshipAwardResource\Pages;

use App\Filament\Resources\ScholarshipAwardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScholarshipAwards extends ListRecords
{
    protected static string $resource = ScholarshipAwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
