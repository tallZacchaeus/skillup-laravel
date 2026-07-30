<?php

namespace App\Filament\Resources\Notifications\WhatsappMessageResource\Pages;

use App\Filament\Resources\Notifications\WhatsappMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWhatsappMessages extends ListRecords
{
    protected static string $resource = WhatsappMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
