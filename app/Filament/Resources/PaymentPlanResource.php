<?php

namespace App\Filament\Resources;

use App\Enums\PaymentPlanStatus;
use App\Filament\Resources\PaymentPlanResource\Pages;
use App\Models\Catalog\PaymentPlan;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PaymentPlanResource extends Resource
{
    protected static ?string $model = PaymentPlan::class;

    protected static ?string $navigationGroup = 'Payments';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 30;

    protected static bool $shouldSkipAuthorization = true;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('order.order_number')->label('Order')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')->formatStateUsing(fn ($state, PaymentPlan $record) => static::money($record->currency, $state))->sortable(),
                Tables\Columns\TextColumn::make('deposit_amount')->formatStateUsing(fn ($state, PaymentPlan $record) => static::money($record->currency, $state))->sortable(),
                Tables\Columns\TextColumn::make('installments_count')->sortable(),
                Tables\Columns\TextColumn::make('interval')->badge()->sortable(),
                Tables\Columns\TextColumn::make('starts_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(static::enumOptions(PaymentPlanStatus::class)),
                Tables\Filters\SelectFilter::make('interval')->options([
                    'weekly' => 'Weekly',
                    'monthly' => 'Monthly',
                    'custom' => 'Custom',
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentPlans::route('/'),
        ];
    }

    private static function enumOptions(string $enum): array
    {
        return collect($enum::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all();
    }

    private static function money(string $currency, mixed $amount): string
    {
        return $currency.' '.number_format((float) $amount, 2);
    }
}
