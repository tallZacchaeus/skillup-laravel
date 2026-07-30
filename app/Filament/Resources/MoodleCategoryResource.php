<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoodleCategoryResource\Pages;
use App\Models\Lms\MoodleConnection;
use App\Models\Lms\MoodleCategory;
use App\Services\Lms\MoodleService;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MoodleCategoryResource extends Resource
{
    protected static ?string $model = MoodleCategory::class;

    protected static ?string $navigationGroup = 'LMS Integration';

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?int $navigationSort = 30;

    protected static bool $shouldSkipAuthorization = true;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('connection.name')->label('Connection')->sortable(),
                Tables\Columns\TextColumn::make('moodle_category_id')->label('Moodle Category ID')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('parent_id')->label('Parent ID')->sortable()->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('syncFromMoodle')
                    ->label('Sync from Moodle')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->action(function (MoodleService $moodleService) {
                        $connection = MoodleConnection::where('is_active', true)->first();
                        if (!$connection) {
                            Notification::make()
                                ->title('Sync failed!')
                                ->body('No active Moodle connection configured.')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            $categories = $moodleService->fetchCategories($connection);
                            $syncedCount = 0;

                            foreach ($categories as $cat) {
                                if (!isset($cat['id'])) {
                                    continue;
                                }

                                MoodleCategory::updateOrCreate(
                                    [
                                        'moodle_connection_id' => $connection->id,
                                        'moodle_category_id' => (string) $cat['id'],
                                    ],
                                    [
                                        'name' => $cat['name'] ?? '',
                                        'parent_id' => isset($cat['parent']) ? (string) $cat['parent'] : null,
                                    ]
                                );
                                $syncedCount++;
                            }

                            Notification::make()
                                ->title('Sync completed successfully!')
                                ->body("Synced {$syncedCount} categories from Moodle.")
                                ->success()
                                ->send();

                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Sync failed!')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMoodleCategories::route('/'),
        ];
    }
}
