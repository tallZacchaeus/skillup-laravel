<?php

namespace App\Filament\Resources\Notifications\NotificationEventResource\Pages;

use App\Filament\Resources\Notifications\NotificationEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotificationEvents extends ListRecords
{
    protected static string $resource = NotificationEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
