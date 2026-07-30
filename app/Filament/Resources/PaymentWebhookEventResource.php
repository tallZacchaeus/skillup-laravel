<?php

namespace App\Filament\Resources;

use App\Enums\WebhookEventStatus;
use App\Filament\Resources\PaymentWebhookEventResource\Pages;
use App\Models\Catalog\PaymentWebhookEvent;
use App\Services\Payments\PaymentService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PaymentWebhookEventResource extends Resource
{
    protected static ?string $model = PaymentWebhookEvent::class;

    protected static ?string $navigationGroup = 'Payments';

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?int $navigationSort = 80;

    protected static bool $shouldSkipAuthorization = true;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('reference')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('event_key')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('error')->limit(60)->toggleable(),
                Tables\Columns\TextColumn::make('processed_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(static::enumOptions(WebhookEventStatus::class)),
                Tables\Filters\SelectFilter::make('event')->options([
                    'charge.success' => 'Charge success',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('retry')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->visible(fn (PaymentWebhookEvent $record) => $record->event === 'charge.success' && filled($record->reference))
                    ->action(function (PaymentWebhookEvent $record): void {
                        app(PaymentService::class)->verifyPaystackReference($record->reference);
                        $record->update([
                            'status' => WebhookEventStatus::Processed,
                            'processed_at' => now(),
                            'error' => null,
                        ]);

                        Notification::make()
                            ->title('Webhook event retried')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentWebhookEvents::route('/'),
        ];
    }

    private static function enumOptions(string $enum): array
    {
        return collect($enum::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all();
    }
}
