<?php

namespace App\Filament\Resources\Content\DownloadableResource\Pages;

use App\Filament\Resources\Content\DownloadableResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDownloadable extends CreateRecord
{
    protected static string $resource = DownloadableResource::class;
}
