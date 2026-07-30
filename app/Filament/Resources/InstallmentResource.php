<?php

namespace App\Filament\Resources;

use App\Enums\InstallmentStatus;
use App\Filament\Resources\InstallmentResource\Pages;
use App\Models\Catalog\Installment;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class InstallmentResource extends Resource
{
    protected static ?string $model = Installment::class;

    protected static ?string $navigationGroup = 'Payments';

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 40;

    protected static bool $shouldSkipAuthorization = true;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')->label('Order')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('paymentPlan.name')->label('Plan')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('installment_number')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')->formatStateUsing(fn ($state, Installment $record) => static::money($record->currency, $state))->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')->formatStateUsing(fn ($state, Installment $record) => static::money($record->currency, $state))->sortable(),
                Tables\Columns\TextColumn::make('due_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(static::enumOptions(InstallmentStatus::class)),
                Tables\Filters\Filter::make('overdue')
                    ->query(fn ($query) => $query->where('status', InstallmentStatus::Pending->value)->whereNotNull('due_at')->where('due_at', '<', now())),
            ])
            ->defaultSort('due_at');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstallments::route('/'),
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
