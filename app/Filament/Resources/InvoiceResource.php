<?php

namespace App\Filament\Resources;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Catalog\Invoice;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationGroup = 'Payments';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 50;

    protected static bool $shouldSkipAuthorization = true;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('order.order_number')->label('Order')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Account')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')->formatStateUsing(fn ($state, Invoice $record) => static::money($record->currency, $state))->sortable(),
                Tables\Columns\TextColumn::make('issued_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('due_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(static::enumOptions(InvoiceStatus::class)),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
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
