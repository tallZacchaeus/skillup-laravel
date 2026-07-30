<?php

namespace App\Filament\Resources\ProgramResource\RelationManagers;

use App\Enums\ProgramEditionStatus;
use App\Filament\Resources\ProgramEditionResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EditionsRelationManager extends RelationManager
{
    protected static string $relationship = 'editions';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('year')->sortable(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        ProgramEditionStatus::RegistrationOpen->value => 'success',
                        ProgramEditionStatus::SoldOut->value, ProgramEditionStatus::Running->value => 'warning',
                        ProgramEditionStatus::Draft->value => 'gray',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('starts_on')->date(),
                Tables\Columns\TextColumn::make('registrations_count')->counts('registrations')->label('Registrations'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('create')
                    ->label('New edition')
                    ->url(fn () => ProgramEditionResource::getUrl('create')),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->url(fn ($record) => ProgramEditionResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
