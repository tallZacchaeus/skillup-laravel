<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountRedemptionResource\Pages;
use App\Models\Catalog\DiscountRedemption;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DiscountRedemptionResource extends Resource
{
    protected static ?string $model = DiscountRedemption::class;

    protected static ?string $navigationGroup = 'Discounts';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 50;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('discountRule.name')->label('Rule')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('total_after_discount')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('locked_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('redeemed_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('discount_rule_id')->relationship('discountRule', 'name')->label('Rule'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'locked' => 'Locked',
                        'redeemed' => 'Redeemed',
                        'released' => 'Released',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscountRedemptions::route('/'),
        ];
    }
}
