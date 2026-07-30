<?php

namespace App\Filament\Resources\Notifications\EmailMessageResource\Pages;

use App\Filament\Resources\Notifications\EmailMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailMessage extends EditRecord
{
    protected static string $resource = EmailMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
