<?php

namespace App\Filament\Resources\Notifications\EmailTemplateResource\Pages;

use App\Filament\Resources\Notifications\EmailTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailTemplate extends CreateRecord
{
    protected static string $resource = EmailTemplateResource::class;
}
