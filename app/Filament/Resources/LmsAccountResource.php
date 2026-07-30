<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LmsAccountResource\Pages;
use App\Models\Lms\LmsAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LmsAccountResource extends Resource
{
    protected static ?string $model = LmsAccount::class;

    protected static ?string $navigationGroup = 'LMS Integration';

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?int $navigationSort = 50;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('moodle_connection_id')
                ->relationship('connection', 'name')
                ->required()
                ->preload(),
            Forms\Components\TextInput::make('moodle_user_id')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('moodle_username')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('sync_status')
                ->options([
                    'active' => 'Active',
                    'suspended' => 'Suspended',
                ])
                ->default('active')
                ->required(),
            Forms\Components\KeyValue::make('metadata')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('connection.name')->label('Connection')->sortable(),
                Tables\Columns\TextColumn::make('moodle_username')->label('Moodle Username')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('moodle_user_id')->label('Moodle User ID')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sync_status')->label('Status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sync_status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLmsAccounts::route('/'),
        ];
    }
}
