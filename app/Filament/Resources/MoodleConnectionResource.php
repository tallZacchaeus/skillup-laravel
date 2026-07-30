<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoodleConnectionResource\Pages;
use App\Models\Lms\MoodleConnection;
use App\Services\Lms\MoodleService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MoodleConnectionResource extends Resource
{
    protected static ?string $model = MoodleConnection::class;

    protected static ?string $navigationGroup = 'LMS Integration';

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('base_url')
                ->required()
                ->url()
                ->maxLength(255),
            Forms\Components\TextInput::make('token')
                ->password()
                ->revealable()
                ->maxLength(255)
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context): bool => $context === 'create'),
            Forms\Components\TextInput::make('service_name')
                ->maxLength(255),
            Forms\Components\Toggle::make('is_active')
                ->default(true),
            Forms\Components\KeyValue::make('metadata')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('base_url')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('testConnection')
                    ->label('Test Connection')
                    ->icon('heroicon-o-bolt')
                    ->color('warning')
                    ->action(function (MoodleConnection $record, MoodleService $moodleService) {
                        $success = $moodleService->testConnection($record);
                        if ($success) {
                            Notification::make()
                                ->title('Connection successful!')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Connection failed!')
                                ->body('Please check your URL, token, and Moodle Web Services configuration.')
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMoodleConnections::route('/'),
            'create' => Pages\CreateMoodleConnection::route('/create'),
            'edit' => Pages\EditMoodleConnection::route('/{record}/edit'),
        ];
    }
}
