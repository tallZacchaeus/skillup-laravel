<?php

namespace App\Services\Operations;

use App\Models\Operations\OperationalHealthCheck;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class OperationalHealthService
{
    public function __construct(
        private readonly OperationalReportService $reports,
    ) {}

    /**
     * @return array<string, array<string, mixed>>
     */
    public function snapshot(): array
    {
        $checks = [
            'database' => $this->databaseCheck(),
            'queue' => $this->queueCheck(),
            'failed_operations' => $this->failedOperationsCheck(),
        ];

        foreach ($checks as $name => $check) {
            OperationalHealthCheck::updateOrCreate(
                ['name' => $name],
                [
                    'status' => $check['status'],
                    'summary' => $check['summary'],
                    'metrics' => $check['metrics'],
                    'checked_at' => now(),
                ]
            );
        }

        return $checks;
    }

    /**
     * @return array<string, mixed>
     */
    private function databaseCheck(): array
    {
        try {
            DB::select('select 1');

            return [
                'status' => 'healthy',
                'summary' => 'Database connection is responding.',
                'metrics' => [],
            ];
        } catch (Throwable $exception) {
            return [
                'status' => 'critical',
                'summary' => $exception->getMessage(),
                'metrics' => [],
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function queueCheck(): array
    {
        $pendingJobs = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        return [
            'status' => $failedJobs > 0 ? 'attention' : 'healthy',
            'summary' => $failedJobs > 0 ? 'Failed queue jobs need review.' : 'No failed queue jobs found.',
            'metrics' => [
                'pending_jobs' => $pendingJobs,
                'failed_jobs' => $failedJobs,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function failedOperationsCheck(): array
    {
        $metrics = $this->reports->failedOperations();
        $totalFailures = array_sum($metrics);

        return [
            'status' => $totalFailures > 0 ? 'attention' : 'healthy',
            'summary' => $totalFailures > 0 ? 'Operational failures are present.' : 'No operational failures found.',
            'metrics' => $metrics,
        ];
    }
}
