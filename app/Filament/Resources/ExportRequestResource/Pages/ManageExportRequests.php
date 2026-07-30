<?php

namespace App\Filament\Resources\ExportRequestResource\Pages;

use App\Filament\Resources\ExportRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;

class ManageExportRequests extends ManageRecords
{
    protected static string $resource = ExportRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(fn (array $data): array => [
                    ...$data,
                    'user_id' => Auth::id(),
                    'status' => 'queued',
                ])
                ->after(fn ($record) => app(\App\Services\Operations\ExportService::class)->generate($record)),
        ];
    }
}
