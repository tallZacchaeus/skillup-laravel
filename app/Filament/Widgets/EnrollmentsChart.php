<?php

namespace App\Filament\Widgets;

use App\Models\Catalog\Enrollment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class EnrollmentsChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'New enrollments — last 8 weeks';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $buckets = [];

        for ($i = 7; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $buckets[$weekStart->toDateString()] = 0;
        }

        Enrollment::where('created_at', '>=', now()->subWeeks(7)->startOfWeek())
            ->get(['created_at'])
            ->each(function (Enrollment $enrollment) use (&$buckets): void {
                $weekStart = Carbon::parse($enrollment->created_at)->startOfWeek()->toDateString();

                if (array_key_exists($weekStart, $buckets)) {
                    $buckets[$weekStart]++;
                }
            });

        return [
            'datasets' => [
                [
                    'label' => 'Enrollments',
                    'data' => array_values($buckets),
                ],
            ],
            'labels' => array_map(
                fn (string $date) => 'Wk of '.Carbon::parse($date)->format('M j'),
                array_keys($buckets),
            ),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
