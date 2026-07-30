<?php

namespace App\Filament\Resources\ProgramEditionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TracksRelationManager extends RelationManager
{
    protected static string $relationship = 'tracks';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(120),
                Forms\Components\TextInput::make('slug')->required(),
                Forms\Components\TextInput::make('age_min')->numeric(),
                Forms\Components\TextInput::make('age_max')->numeric(),
                Forms\Components\TextInput::make('capacity')->numeric(),
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'title')
                    ->searchable()
                    ->preload()
                    ->helperText('The product that carries pricing & checkout for this track.'),
                Forms\Components\Textarea::make('summary')->rows(3)->columnSpanFull(),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('semibold'),
                Tables\Columns\TextColumn::make('age_min')->label('Age from'),
                Tables\Columns\TextColumn::make('age_max')->label('Age to'),
                Tables\Columns\TextColumn::make('capacity'),
                Tables\Columns\TextColumn::make('seats_taken')
                    ->label('Seats taken')
                    ->state(fn ($record) => $record->seatsTaken()),
                Tables\Columns\TextColumn::make('product.title')->label('Product')->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
