<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LmsApiLogResource\Pages;
use App\Models\Lms\LmsApiLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LmsApiLogResource extends Resource
{
    protected static ?string $model = LmsApiLog::class;

    protected static ?string $navigationGroup = 'LMS Integration';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 30;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('moodle_connection_id')
                ->relationship('connection', 'name')
                ->disabled(),
            Forms\Components\TextInput::make('endpoint')
                ->disabled(),
            Forms\Components\TextInput::make('response_status')
                ->disabled(),
            Forms\Components\TextInput::make('duration_ms')
                ->numeric()
                ->disabled(),
            Forms\Components\KeyValue::make('request_payload')
                ->disabled()
                ->columnSpanFull(),
            Forms\Components\KeyValue::make('response_payload')
                ->disabled()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('connection.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('endpoint')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('response_status')->sortable(),
                Tables\Columns\TextColumn::make('duration_ms')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLmsApiLogs::route('/'),
            'view' => Pages\ViewLmsApiLog::route('/{record}'),
        ];
    }
}
