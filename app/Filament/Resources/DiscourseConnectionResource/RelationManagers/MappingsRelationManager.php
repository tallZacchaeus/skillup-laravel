<?php

namespace App\Filament\Resources\DiscourseConnectionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MappingsRelationManager extends RelationManager
{
    protected static string $relationship = 'mappings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('discourse_group_id')
                    ->label('Discourse Group')
                    ->options(fn ($livewire) => $livewire->getOwnerRecord()->groups->pluck('name', 'id'))
                    ->required(),
                Forms\Components\Select::make('mappable_type')
                    ->options([
                        \App\Models\Catalog\Product::class => 'Product',
                        \App\Models\Catalog\Cohort::class => 'Cohort',
                        \App\Models\Catalog\Track::class => 'Track',
                    ])
                    ->required()
                    ->reactive(),
                Forms\Components\Select::make('mappable_id')
                    ->label('Mapped Item')
                    ->options(function (callable $get) {
                        $type = $get('mappable_type');
                        if ($type === \App\Models\Catalog\Product::class) {
                            return \App\Models\Catalog\Product::pluck('title', 'id');
                        } elseif ($type === \App\Models\Catalog\Cohort::class) {
                            return \App\Models\Catalog\Cohort::pluck('title', 'id');
                        } elseif ($type === \App\Models\Catalog\Track::class) {
                            return \App\Models\Catalog\Track::pluck('title', 'id');
                        }
                        return [];
                    })
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group.name')
                    ->label('Discourse Group')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mappable_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => class_basename($state)),
                Tables\Columns\TextColumn::make('mappable_id')
                    ->label('Mapped Item')
                    ->formatStateUsing(function ($record) {
                        $model = $record->mappable;
                        if (!$model) {
                            return "Unknown ID: " . $record->mappable_id;
                        }
                        return $model->title ?? $model->name ?? $record->mappable_id;
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['discourse_connection_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
