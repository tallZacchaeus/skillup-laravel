<?php

namespace App\Filament\Resources\Notifications\WhatsappMessageResource\Pages;

use App\Filament\Resources\Notifications\WhatsappMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWhatsappMessage extends EditRecord
{
    protected static string $resource = WhatsappMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
