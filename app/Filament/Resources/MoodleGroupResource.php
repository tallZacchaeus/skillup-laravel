<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoodleGroupResource\Pages;
use App\Models\Lms\MoodleConnection;
use App\Models\Lms\MoodleCourse;
use App\Models\Lms\MoodleGroup;
use App\Services\Lms\MoodleService;
use Exception;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MoodleGroupResource extends Resource
{
    protected static ?string $model = MoodleGroup::class;

    protected static ?string $navigationGroup = 'LMS Integration';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 40;

    protected static bool $shouldSkipAuthorization = true;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('connection.name')->label('Connection')->sortable(),
                Tables\Columns\TextColumn::make('moodle_group_id')->label('Moodle Group ID')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('moodle_course_id')->label('Moodle Course ID')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('syncGroupsForCourse')
                    ->label('Sync Groups for Course')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('moodle_course_id')
                            ->label('Select Course')
                            ->options(fn () => MoodleCourse::pluck('fullname', 'moodle_course_id')->toArray())
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data, MoodleService $moodleService) {
                        $connection = MoodleConnection::where('is_active', true)->first();
                        if (!$connection) {
                            Notification::make()
                                ->title('Sync failed!')
                                ->body('No active Moodle connection configured.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $moodleCourseId = $data['moodle_course_id'];

                        try {
                            $groups = $moodleService->fetchGroups($connection, $moodleCourseId);
                            $syncedCount = 0;

                            foreach ($groups as $g) {
                                if (!isset($g['id'])) {
                                    continue;
                                }

                                MoodleGroup::updateOrCreate(
                                    [
                                        'moodle_connection_id' => $connection->id,
                                        'moodle_group_id' => (string) $g['id'],
                                    ],
                                    [
                                        'moodle_course_id' => $moodleCourseId,
                                        'name' => $g['name'] ?? '',
                                    ]
                                );
                                $syncedCount++;
                            }

                            Notification::make()
                                ->title('Sync completed!')
                                ->body("Synced {$syncedCount} groups for Moodle course ID {$moodleCourseId}.")
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
            'index' => Pages\ListMoodleGroups::route('/'),
        ];
    }
}
