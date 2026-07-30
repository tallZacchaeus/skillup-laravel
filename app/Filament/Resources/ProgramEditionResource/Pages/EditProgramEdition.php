<?php

namespace App\Filament\Resources\ProgramEditionResource\Pages;

use App\Enums\ProgramEditionStatus;
use App\Filament\Resources\ProgramEditionResource;
use App\Models\Programs\ProgramEdition;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProgramEdition extends EditRecord
{
    protected static string $resource = ProgramEditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('duplicate')
                ->label('Duplicate for next year')
                ->icon('heroicon-o-document-duplicate')
                ->requiresConfirmation()
                ->modalDescription('Copies this edition — content, tracks, registration fields — as a draft for the next year. Dates are cleared and track products are left empty so pricing is decided fresh.')
                ->action(function (ProgramEdition $record) {
                    $nextYear = $record->year + 1;

                    $copy = $record->replicate([
                        'starts_on', 'ends_on', 'age_reference_date',
                    ]);
                    $copy->year = $nextYear;
                    $copy->slug = (string) $nextYear;
                    $copy->title = str_replace((string) $record->year, (string) $nextYear, $record->title);
                    $copy->status = ProgramEditionStatus::Draft;
                    $copy->save();

                    $record->tracks->each(function ($track) use ($copy) {
                        $trackCopy = $track->replicate(['product_id']);
                        $trackCopy->program_edition_id = $copy->id;
                        $trackCopy->save();
                    });

                    Notification::make()
                        ->success()
                        ->title("{$copy->title} created as a draft")
                        ->body('Set dates, attach products with this year\'s pricing, and publish when ready.')
                        ->send();

                    return redirect(ProgramEditionResource::getUrl('edit', ['record' => $copy]));
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
