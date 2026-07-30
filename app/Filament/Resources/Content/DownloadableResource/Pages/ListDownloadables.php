<?php

namespace App\Filament\Resources\Content\DownloadableResource\Pages;

use App\Filament\Resources\Content\DownloadableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDownloadables extends ListRecords
{
    protected static string $resource = DownloadableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
