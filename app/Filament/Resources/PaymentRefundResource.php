<?php

namespace App\Filament\Resources;

use App\Enums\RefundStatus;
use App\Filament\Resources\PaymentRefundResource\Pages;
use App\Models\Catalog\PaymentRefund;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PaymentRefundResource extends Resource
{
    protected static ?string $model = PaymentRefund::class;

    protected static ?string $navigationGroup = 'Payments';

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';

    protected static ?int $navigationSort = 70;

    protected static bool $shouldSkipAuthorization = true;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('order.order_number')->label('Order')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('payment.reference')->label('Payment')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')->formatStateUsing(fn ($state, PaymentRefund $record) => static::money($record->currency, $state))->sortable(),
                Tables\Columns\TextColumn::make('requested_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('processed_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(static::enumOptions(RefundStatus::class)),
            ])
            ->actions([
                Tables\Actions\Action::make('markProcessing')
                    ->label('Processing')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (PaymentRefund $record) => $record->status === RefundStatus::Pending)
                    ->action(fn (PaymentRefund $record) => static::setStatus($record, RefundStatus::Processing)),
                Tables\Actions\Action::make('markProcessed')
                    ->label('Processed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('provider_refund_id')->maxLength(255),
                    ])
                    ->visible(fn (PaymentRefund $record) => $record->status !== RefundStatus::Processed)
                    ->action(function (PaymentRefund $record, array $data): void {
                        $record->update([
                            'status' => RefundStatus::Processed,
                            'provider_refund_id' => $data['provider_refund_id'] ?? $record->provider_refund_id,
                            'processed_at' => now(),
                        ]);

                        Notification::make()->title('Refund marked processed')->success()->send();
                    }),
                Tables\Actions\Action::make('markFailed')
                    ->label('Failed')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PaymentRefund $record) => ! in_array($record->status, [RefundStatus::Processed, RefundStatus::Failed], true))
                    ->action(fn (PaymentRefund $record) => static::setStatus($record, RefundStatus::Failed)),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentRefunds::route('/'),
        ];
    }

    private static function setStatus(PaymentRefund $refund, RefundStatus $status): void
    {
        $refund->update([
            'status' => $status,
            'processed_at' => $status === RefundStatus::Processed ? now() : $refund->processed_at,
        ]);

        Notification::make()
            ->title('Refund status updated')
            ->success()
            ->send();
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
