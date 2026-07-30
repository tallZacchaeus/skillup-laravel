<?php

namespace App\Filament\Corporate\Widgets;

use App\Filament\Corporate\Concerns\ScopesCorporateAccount;
use App\Models\Catalog\Enrollment;
use Filament\Widgets\ChartWidget;

/**
 * New-enrollments-per-month trend, built from real enrollment `created_at`
 * timestamps. Hides itself when the organisation has no enrollments, so an
 * empty chart is never shown. A text heading + description keep the meaning
 * available to assistive tech alongside the canvas.
 */
class CorporateEnrollmentTrend extends ChartWidget
{
    use ScopesCorporateAccount;

    protected static ?string $heading = 'Enrollment trend';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return Enrollment::whereIn('corporate_account_id', static::corporateAccountIds())->exists();
    }

    public function getDescription(): ?string
    {
        return 'New team enrollments per month over the last 6 months.';
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $accountIds = static::corporateAccountIds();
        $labels = [];
        $counts = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');
            $counts[] = Enrollment::whereIn('corporate_account_id', $accountIds)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return [
            'datasets' => [[
                'label' => 'New enrollments',
                'data' => $counts,
                'borderColor' => '#1E3A8A',
                'backgroundColor' => 'rgba(30, 58, 138, 0.10)',
                'fill' => true,
                'tension' => 0.3,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
