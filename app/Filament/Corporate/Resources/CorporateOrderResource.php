<?php

namespace App\Filament\Corporate\Resources;

use App\Filament\Corporate\Concerns\ScopesCorporateAccount;
use App\Filament\Corporate\Resources\CorporateOrderResource\Pages;
use App\Models\Catalog\Order;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CorporateOrderResource extends Resource
{
    use ScopesCorporateAccount;

    protected static ?string $model = Order::class;

    protected static ?string $navigationGroup = 'Billing';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Invoices And Payments';

    protected static ?int $navigationSort = 30;

    protected static bool $shouldSkipAuthorization = true;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('corporate_account_id', static::corporateAccountIds())
            ->with(['corporateAccount', 'items.product']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('corporateAccount.name')->label('Company')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('balance_due')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                        'partially_paid' => 'Partially paid',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCorporateOrders::route('/'),
        ];
    }
}
