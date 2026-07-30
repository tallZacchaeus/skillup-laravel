<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Throwable;

class DeploymentSmokeTestCommand extends Command
{
    protected $signature = 'skillup:smoke-test {--url= : Base URL for HTTP smoke tests}';

    protected $description = 'Run route-level or HTTP smoke tests for deployment verification.';

    public function handle(): int
    {
        $baseUrl = $this->option('url');

        if (blank($baseUrl)) {
            return $this->runRouteSmokeTests();
        }

        return $this->runHttpSmokeTests(rtrim((string) $baseUrl, '/'));
    }

    private function runRouteSmokeTests(): int
    {
        $requiredRoutes = [
            'deployment.live',
            'deployment.ready',
            'home',
            'courses.index',
            'checkout.status.failed',
            'webhooks.paystack',
            'discourse.sso',
        ];

        $missing = collect($requiredRoutes)
            ->reject(fn (string $name) => Route::has($name))
            ->values();

        if ($missing->isNotEmpty()) {
            $this->error('Missing smoke-test routes: '.$missing->implode(', '));

            return self::FAILURE;
        }

        $this->info('Route smoke tests passed.');

        return self::SUCCESS;
    }

    private function runHttpSmokeTests(string $baseUrl): int
    {
        $failed = [];

        foreach (config('deployment.smoke.paths', []) as $path) {
            $url = $baseUrl.'/'.ltrim($path, '/');

            try {
                $response = Http::timeout(10)->get($url);

                if (! $response->successful()) {
                    $failed[] = "{$path} returned {$response->status()}";
                }
            } catch (Throwable $exception) {
                $failed[] = "{$path} failed: {$exception->getMessage()}";
            }
        }

        if ($failed !== []) {
            foreach ($failed as $failure) {
                $this->error($failure);
            }

            return self::FAILURE;
        }

        $this->info('HTTP smoke tests passed.');

        return self::SUCCESS;
    }
}
