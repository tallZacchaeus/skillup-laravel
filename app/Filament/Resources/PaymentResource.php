<?php

namespace App\Filament\Resources;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Catalog\Payment;
use App\Services\Payments\PaymentService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationGroup = 'Payments';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 20;

    protected static bool $shouldSkipAuthorization = true;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('order.order_number')->label('Order')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Account')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('provider')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')->formatStateUsing(fn ($state, Payment $record) => static::money($record->currency, $state))->sortable(),
                Tables\Columns\TextColumn::make('channel')->toggleable(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider')->options([
                    'paystack' => 'Paystack',
                    'manual' => 'Manual',
                ]),
                Tables\Filters\SelectFilter::make('status')->options(static::enumOptions(PaymentStatus::class)),
            ])
            ->actions([
                Tables\Actions\Action::make('verify')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->visible(fn (Payment $record) => $record->provider === 'paystack' && $record->status !== PaymentStatus::Paid)
                    ->action(function (Payment $record): void {
                        app(PaymentService::class)->verifyPaystackReference($record->reference);

                        Notification::make()
                            ->title('Paystack reference verified')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('openAuthorization')
                    ->label('Open checkout')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Payment $record) => $record->authorization_url)
                    ->openUrlInNewTab()
                    ->visible(fn (Payment $record) => filled($record->authorization_url)),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
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
