<?php

namespace App\Filament\Widgets;

use App\Models\Catalog\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Revenue — last 30 days';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $buckets = [];

        for ($i = 29; $i >= 0; $i--) {
            $buckets[now()->subDays($i)->toDateString()] = 0.0;
        }

        Order::where('paid_at', '>=', now()->subDays(29)->startOfDay())
            ->get(['paid_at', 'amount_paid'])
            ->each(function (Order $order) use (&$buckets): void {
                $date = Carbon::parse($order->paid_at)->toDateString();

                if (array_key_exists($date, $buckets)) {
                    $buckets[$date] += (float) $order->amount_paid;
                }
            });

        return [
            'datasets' => [
                [
                    'label' => 'Amount paid',
                    'data' => array_values($buckets),
                    'fill' => 'start',
                    'tension' => 0.3,
                ],
            ],
            'labels' => array_map(
                fn (string $date) => Carbon::parse($date)->format('M j'),
                array_keys($buckets),
            ),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
