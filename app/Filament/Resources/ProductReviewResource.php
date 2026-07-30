<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReviewResource\Pages;
use App\Models\Catalog\ProductReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Reviews';

    protected static ?int $navigationSort = 30;

    protected static bool $shouldSkipAuthorization = true;

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('is_published', false)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')
                ->label('Course')
                ->relationship('product', 'title')
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('reviewer_name')->required()->maxLength(255),
            Forms\Components\TextInput::make('reviewer_title')->maxLength(255),
            Forms\Components\Select::make('rating')
                ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
                ->required(),
            Forms\Components\TextInput::make('title')->maxLength(255)->columnSpanFull(),
            Forms\Components\Textarea::make('body')->required()->rows(4)->columnSpanFull(),
            Forms\Components\Toggle::make('is_verified')->label('Verified learner'),
            Forms\Components\Toggle::make('is_published')->label('Published')->default(true),
            Forms\Components\DateTimePicker::make('reviewed_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.title')->label('Course')->searchable()->limit(30),
                Tables\Columns\TextColumn::make('reviewer_name')->searchable(),
                Tables\Columns\TextColumn::make('rating')->badge()->sortable(),
                Tables\Columns\IconColumn::make('is_verified')->boolean()->label('Verified'),
                Tables\Columns\ToggleColumn::make('is_published')->label('Published'),
                Tables\Columns\TextColumn::make('reviewed_at')->dateTime()->sortable(),
            ])
            ->defaultSort('reviewed_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')->label('Published'),
                Tables\Filters\SelectFilter::make('rating')->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']),
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
            'index' => Pages\ListProductReviews::route('/'),
            'create' => Pages\CreateProductReview::route('/create'),
            'edit' => Pages\EditProductReview::route('/{record}/edit'),
        ];
    }
}
