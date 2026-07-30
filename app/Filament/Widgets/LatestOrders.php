<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Catalog\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Str;

class LatestOrders extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Latest orders';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(10))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->url(fn () => OrderResource::getUrl('index'))
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('user.email')->label('Account')->default('—'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state)),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state)),
                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(fn ($state, Order $record) => $record->currency.' '.number_format((float) $state, 2)),
                Tables\Columns\TextColumn::make('created_at')->since(),
            ])
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Orders will appear here as soon as checkout goes live.');
    }
}
