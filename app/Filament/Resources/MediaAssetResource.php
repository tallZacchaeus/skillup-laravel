<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaAssetResource\Pages;
use App\Models\Content\MediaAsset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MediaAssetResource extends Resource
{
    protected static ?string $model = MediaAsset::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $navigationLabel = 'Media Library';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('file_path')
                    ->required()
                    ->directory('media-library')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('disk')
                    ->default('public')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('collection')
                    ->maxLength(255),
                Forms\Components\TextInput::make('alt_text')
                    ->maxLength(255),
                Forms\Components\Textarea::make('caption')
                    ->maxLength(1000)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('mime_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('size')
                    ->numeric(),
                Forms\Components\KeyValue::make('metadata')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_public')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('collection')
                    ->badge()
                    ->placeholder('General')
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_path')
                    ->searchable()
                    ->limit(48),
                Tables\Columns\TextColumn::make('mime_type')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_public')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_public'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMediaAssets::route('/'),
        ];
    }
}
