<?php

namespace App\Filament\Widgets;

use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Order;
use App\Models\Content\Lead;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BusinessOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $currency = Order::query()->value('currency') ?? 'NGN';
        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth();

        $revenueThisMonth = (float) Order::where('paid_at', '>=', $monthStart)->sum('amount_paid');
        $revenueLastMonth = (float) Order::whereBetween('paid_at', [$lastMonthStart, $monthStart])->sum('amount_paid');
        $revenueTrend = $this->dailySeries(
            Order::where('paid_at', '>=', now()->subDays(6)->startOfDay())->get(['paid_at', 'amount_paid']),
            fn ($order) => $order->paid_at,
            fn ($order) => (float) $order->amount_paid,
        );

        $paidOrdersThisMonth = Order::whereIn('status', [OrderStatus::Paid->value, OrderStatus::PartiallyPaid->value])
            ->where('paid_at', '>=', $monthStart)
            ->count();
        $awaitingPayment = Order::where('status', OrderStatus::PendingPayment->value)->count();

        $activeEnrollments = Enrollment::where('status', EnrollmentStatus::Active->value)->count();
        $pendingEnrollments = Enrollment::where('status', EnrollmentStatus::Pending->value)->count();

        $leadsThisWeek = Lead::where('created_at', '>=', now()->subDays(7))->count();
        $leadsPreviousWeek = Lead::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();

        return [
            Stat::make('Revenue (month to date)', $currency.' '.number_format($revenueThisMonth, 2))
                ->description($currency.' '.number_format($revenueLastMonth, 2).' last month')
                ->descriptionIcon($revenueThisMonth >= $revenueLastMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueThisMonth >= $revenueLastMonth ? 'success' : 'warning')
                ->chart($revenueTrend),

            Stat::make('Paid orders (month to date)', number_format($paidOrdersThisMonth))
                ->description(number_format($awaitingPayment).' awaiting payment')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make('Active enrollments', number_format($activeEnrollments))
                ->description(number_format($pendingEnrollments).' pending provisioning')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color($pendingEnrollments > 0 ? 'warning' : 'success'),

            Stat::make('Leads (last 7 days)', number_format($leadsThisWeek))
                ->description(number_format($leadsPreviousWeek).' the week before')
                ->descriptionIcon($leadsThisWeek >= $leadsPreviousWeek ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($leadsThisWeek >= $leadsPreviousWeek ? 'success' : 'warning'),
        ];
    }

    /**
     * Sum values into one bucket per day for the trailing 7 days.
     *
     * @return array<int, float>
     */
    private function dailySeries($records, callable $dateOf, callable $valueOf): array
    {
        $buckets = [];

        for ($i = 6; $i >= 0; $i--) {
            $buckets[now()->subDays($i)->toDateString()] = 0.0;
        }

        foreach ($records as $record) {
            $date = Carbon::parse($dateOf($record))->toDateString();

            if (array_key_exists($date, $buckets)) {
                $buckets[$date] += $valueOf($record);
            }
        }

        return array_values($buckets);
    }
}
