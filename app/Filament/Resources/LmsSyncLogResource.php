<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LmsSyncLogResource\Pages;
use App\Models\Lms\LmsSyncLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Models\Catalog\Enrollment;
use App\Enums\EnrollmentStatus;

class LmsSyncLogResource extends Resource
{
    protected static ?string $model = LmsSyncLog::class;

    protected static ?string $navigationGroup = 'LMS Integration';

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?int $navigationSort = 20;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('enrollment_id')
                ->relationship('enrollment', 'id')
                ->disabled(),
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->disabled(),
            Forms\Components\TextInput::make('action')
                ->disabled(),
            Forms\Components\TextInput::make('status')
                ->disabled(),
            Forms\Components\TextInput::make('attempts')
                ->numeric()
                ->disabled(),
            Forms\Components\Textarea::make('error_message')
                ->disabled()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('action')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'success' => 'success',
                    'partial' => 'warning',
                    'failed' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('attempts')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('error_message')->limit(50)->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'success' => 'Success',
                    'partial' => 'Partial',
                    'failed' => 'Failed',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('retryMoodle')
                    ->label('Retry Provisioning')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn (LmsSyncLog $record) => in_array($record->status, ['failed', 'partial']) && $record->enrollment_id !== null)
                    ->action(function (LmsSyncLog $record) {
                        $enrollment = Enrollment::find($record->enrollment_id);
                        if ($enrollment) {
                            \App\Jobs\Lms\EnrollUserInMoodleJob::dispatch($enrollment);
                            Notification::make()
                                ->title('Job Dispatched')
                                ->body('Moodle enrollment provisioning job has been dispatched.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Enrollment not found')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLmsSyncLogs::route('/'),
            'view' => Pages\ViewLmsSyncLog::route('/{record}'),
        ];
    }
}
