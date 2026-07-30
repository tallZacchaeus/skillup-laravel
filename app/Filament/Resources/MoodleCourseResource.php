<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoodleCourseResource\Pages;
use App\Models\Lms\MoodleConnection;
use App\Models\Lms\MoodleCourse;
use App\Services\Lms\MoodleService;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MoodleCourseResource extends Resource
{
    protected static ?string $model = MoodleCourse::class;

    protected static ?string $navigationGroup = 'LMS Integration';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 20;

    protected static bool $shouldSkipAuthorization = true;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('connection.name')->label('Connection')->sortable(),
                Tables\Columns\TextColumn::make('moodle_course_id')->label('Moodle Course ID')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('shortname')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('fullname')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('summary')->limit(50)->toggleable(),
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
                            $courses = $moodleService->fetchCourses($connection);
                            $syncedCount = 0;

                            foreach ($courses as $c) {
                                if (!isset($c['id'])) {
                                    continue;
                                }

                                MoodleCourse::updateOrCreate(
                                    [
                                        'moodle_connection_id' => $connection->id,
                                        'moodle_course_id' => (string) $c['id'],
                                    ],
                                    [
                                        'shortname' => $c['shortname'] ?? '',
                                        'fullname' => $c['fullname'] ?? '',
                                        'summary' => $c['summary'] ?? null,
                                    ]
                                );
                                $syncedCount++;
                            }

                            Notification::make()
                                ->title('Sync completed successfully!')
                                ->body("Synced {$syncedCount} courses from Moodle.")
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
            'index' => Pages\ListMoodleCourses::route('/'),
        ];
    }
}
