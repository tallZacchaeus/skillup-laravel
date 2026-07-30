<?php

namespace App\Filament\Resources\DiscourseConnectionResource\Pages;

use App\Filament\Resources\DiscourseConnectionResource;
use App\Services\Discourse\DiscourseApiService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDiscourseConnection extends EditRecord
{
    protected static string $resource = DiscourseConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('health_check')
                ->label('Check Connection Health')
                ->color('success')
                ->icon('heroicon-o-shield-check')
                ->action(function (DiscourseApiService $apiService) {
                    $record = $this->getRecord();
                    $isHealthy = $apiService->checkHealth($record);

                    if ($isHealthy) {
                        Notification::make()
                            ->title('Discourse Connection Healthy!')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Discourse Connection Failed!')
                            ->body('Please verify base URL, API key, and API username.')
                            ->danger()
                            ->send();
                    }
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
