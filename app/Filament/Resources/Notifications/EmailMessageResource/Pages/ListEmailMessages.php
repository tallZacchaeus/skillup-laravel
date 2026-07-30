<?php

namespace App\Filament\Resources\Notifications\EmailMessageResource\Pages;

use App\Filament\Resources\Notifications\EmailMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmailMessages extends ListRecords
{
    protected static string $resource = EmailMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
