<?php

namespace App\Filament\Resources\Content\PostResource\Pages;

use App\Filament\Resources\Content\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;
}
