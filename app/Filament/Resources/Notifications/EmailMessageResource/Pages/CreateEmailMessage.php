<?php

namespace App\Filament\Resources\Notifications\EmailMessageResource\Pages;

use App\Filament\Resources\Notifications\EmailMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailMessage extends CreateRecord
{
    protected static string $resource = EmailMessageResource::class;
}
