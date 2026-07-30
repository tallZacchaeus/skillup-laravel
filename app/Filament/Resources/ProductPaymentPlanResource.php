<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductPaymentPlanResource\Pages;
use App\Models\Catalog\ProductPaymentPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductPaymentPlanResource extends Resource
{
    protected static ?string $model = ProductPaymentPlan::class;

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 60;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')
                ->relationship('product', 'title')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
            Forms\Components\TextInput::make('slug')->required()->maxLength(255),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
            Forms\Components\TextInput::make('currency')->required()->maxLength(3)->default('NGN'),
            Forms\Components\TextInput::make('deposit_amount')->numeric()->required()->minValue(0),
            Forms\Components\TextInput::make('installment_amount')->numeric()->required()->minValue(0),
            Forms\Components\TextInput::make('installments_count')->numeric()->required()->minValue(1),
            Forms\Components\Select::make('interval')
                ->options([
                    'weekly' => 'Weekly',
                    'monthly' => 'Monthly',
                    'custom' => 'Custom',
                ])
                ->required(),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\KeyValue::make('metadata')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('deposit_amount')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('installment_amount')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('installments_count')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')->relationship('product', 'title')->label('Product'),
                Tables\Filters\TernaryFilter::make('is_active'),
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
            'index' => Pages\ListProductPaymentPlans::route('/'),
            'create' => Pages\CreateProductPaymentPlan::route('/create'),
            'edit' => Pages\EditProductPaymentPlan::route('/{record}/edit'),
        ];
    }
}
