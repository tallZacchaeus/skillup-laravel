<?php

namespace App\Filament\Resources\Notifications\EmailMessageResource\Pages;

use App\Filament\Resources\Notifications\EmailMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmailMessage extends ViewRecord
{
    protected static string $resource = EmailMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
