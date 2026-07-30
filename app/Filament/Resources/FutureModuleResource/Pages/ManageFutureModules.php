<?php

namespace App\Filament\Resources\FutureModuleResource\Pages;

use App\Filament\Resources\FutureModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFutureModules extends ManageRecords
{
    protected static string $resource = FutureModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
