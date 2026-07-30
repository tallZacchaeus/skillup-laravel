<?php

namespace App\Filament\Resources\Notifications\NotificationEventResource\Pages;

use App\Filament\Resources\Notifications\NotificationEventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotificationEvent extends EditRecord
{
    protected static string $resource = NotificationEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
