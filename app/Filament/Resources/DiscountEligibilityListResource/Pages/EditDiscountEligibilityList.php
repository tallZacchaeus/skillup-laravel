<?php

namespace App\Filament\Resources\DiscountEligibilityListResource\Pages;

use App\Filament\Resources\DiscountEligibilityListResource;
use App\Models\Catalog\DiscountEligibilityList;
use App\Services\Discounts\DiscountEligibilityImporter;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditDiscountEligibilityList extends EditRecord
{
    protected static string $resource = DiscountEligibilityListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importEmails')
                ->label('Import emails')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('CSV, XLSX, or ODS file')
                        ->disk('local')
                        ->directory('discount-imports')
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.oasis.opendocument.spreadsheet',
                        ])
                        ->required(),
                ])
                ->action(function (array $data, DiscountEligibilityImporter $importer) {
                    $file = is_array($data['file']) ? reset($data['file']) : $data['file'];
                    $path = Storage::disk('local')->path($file);

                    /** @var DiscountEligibilityList $record */
                    $record = $this->record;
                    $result = $importer->import($record, $path, basename((string) $file));

                    Notification::make()
                        ->title('Email import complete')
                        ->body("Imported {$result['imported']}; duplicates {$result['duplicates']}; invalid {$result['invalid']}.")
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
