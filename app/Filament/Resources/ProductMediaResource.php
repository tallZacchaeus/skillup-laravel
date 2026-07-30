<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductMediaResource\Pages;
use App\Models\Catalog\ProductMedia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductMediaResource extends Resource
{
    protected static ?string $model = ProductMedia::class;

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 70;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')
                ->relationship('product', 'title')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('type')
                ->options([
                    'image' => 'Image',
                    'video' => 'Video',
                    'document' => 'Document',
                ])
                ->default('image')
                ->required(),
            Forms\Components\TextInput::make('disk')
                ->default('public')
                ->maxLength(255),
            Forms\Components\TextInput::make('path')
                ->helperText('Use a public path such as /images/example.jpg or a storage path on the selected disk.')
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\TextInput::make('url')
                ->url()
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\TextInput::make('alt_text')
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\Toggle::make('is_primary'),
            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->default(0),
            Forms\Components\KeyValue::make('metadata')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('path')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('url')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_primary')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')->relationship('product', 'title')->label('Product'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'image' => 'Image',
                        'video' => 'Video',
                        'document' => 'Document',
                    ]),
                Tables\Filters\TernaryFilter::make('is_primary'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListProductMedia::route('/'),
            'create' => Pages\CreateProductMedia::route('/create'),
            'edit' => Pages\EditProductMedia::route('/{record}/edit'),
        ];
    }
}
