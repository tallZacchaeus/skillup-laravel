<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeploymentHealthController extends Controller
{
    public function live(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => config('app.name'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function ready(): JsonResponse
    {
        $checks = [
            'app_key' => $this->check(fn () => filled(config('app.key')), 'Application key is configured.'),
            'database' => $this->check(function (): bool {
                DB::connection()->getPdo();

                return true;
            }, 'Database connection is reachable.'),
            'cache' => $this->check(function (): bool {
                Cache::put('skillup:ready-check', now()->timestamp, now()->addMinute());

                return Cache::has('skillup:ready-check');
            }, 'Cache store accepts writes.'),
            'storage' => $this->check(fn () => is_writable(storage_path('framework')), 'Storage framework directory is writable.'),
            'queue' => $this->check(fn () => config('queue.default') !== 'sync', 'Queue driver is asynchronous.'),
        ];

        $ready = collect($checks)->every(fn (array $check) => $check['ok']);

        return response()->json([
            'status' => $ready ? 'ok' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toISOString(),
        ], $ready ? 200 : 503);
    }

    /**
     * @param  callable(): bool  $callback
     * @return array{ok: bool, message: string}
     */
    private function check(callable $callback, string $successMessage): array
    {
        try {
            $ok = (bool) $callback();

            return [
                'ok' => $ok,
                'message' => $ok ? $successMessage : 'Check returned false.',
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'message' => $exception->getMessage(),
            ];
        }
    }
}
