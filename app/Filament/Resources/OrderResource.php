<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Catalog\Order;
use App\Services\Payments\PaymentService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $recordTitleAttribute = 'order_number';

    protected static ?string $navigationGroup = 'Payments';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldSkipAuthorization = true;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Account')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('customer_email')
                    ->label('Customer')
                    ->state(fn (Order $record) => data_get($record->metadata, 'customer.email')),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')->formatStateUsing(fn ($state, Order $record) => static::money($record->currency, $state))->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')->formatStateUsing(fn ($state, Order $record) => static::money($record->currency, $state))->sortable(),
                Tables\Columns\TextColumn::make('balance_due')->formatStateUsing(fn ($state, Order $record) => static::money($record->currency, $state))->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(static::enumOptions(OrderStatus::class)),
                Tables\Filters\SelectFilter::make('payment_status')->options(static::enumOptions(PaymentStatus::class)),
            ])
            ->actions([
                Tables\Actions\Action::make('recordManualPayment')
                    ->label('Manual payment')
                    ->icon('heroicon-o-banknotes')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        Forms\Components\Textarea::make('note')->rows(3),
                    ])
                    ->visible(fn (Order $record) => (float) $record->balance_due > 0 && $record->status !== OrderStatus::Cancelled)
                    ->action(function (Order $record, array $data): void {
                        app(PaymentService::class)->recordManualPayment($record, (float) $data['amount'], auth()->user(), $data['note'] ?? null);

                        Notification::make()
                            ->title('Manual payment recorded')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('requestRefund')
                    ->label('Request refund')
                    ->icon('heroicon-o-receipt-refund')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(fn (Order $record) => (float) $record->amount_paid)
                            ->required(),
                        Forms\Components\Textarea::make('reason')->rows(3),
                    ])
                    ->visible(fn (Order $record) => (float) $record->amount_paid > 0)
                    ->action(function (Order $record, array $data): void {
                        app(PaymentService::class)->createRefundRequest($record, (float) $data['amount'], $data['reason'] ?? null, auth()->user());

                        Notification::make()
                            ->title('Refund request created')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('cancelOrder')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')->rows(3),
                    ])
                    ->visible(fn (Order $record) => $record->status !== OrderStatus::Cancelled)
                    ->action(function (Order $record, array $data): void {
                        app(PaymentService::class)->cancelOrder($record, $data['reason'] ?? null);

                        Notification::make()
                            ->title('Order cancelled')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
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
