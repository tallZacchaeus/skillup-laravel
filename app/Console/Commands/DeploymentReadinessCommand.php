<?php

namespace App\Console\Commands;

use App\Models\Catalog\Product;
use App\Models\Discourse\DiscourseConnection;
use App\Models\Lms\MoodleConnection;
use App\Models\Notifications\NotificationEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Throwable;

class DeploymentReadinessCommand extends Command
{
    protected $signature = 'skillup:deployment-readiness
        {--production : Fail when production-blocking checks are missing}
        {--json : Output machine-readable JSON}';

    protected $description = 'Check whether the SkillUp platform is ready for production deployment and cutover.';

    /**
     * @var array<int, array{area: string, name: string, status: string, message: string}>
     */
    private array $results = [];

    public function handle(): int
    {
        $production = (bool) $this->option('production');

        $this->check('Security', 'No secrets committed to env templates', $this->envTemplatesLookSafe(), 'Only placeholders found in env templates.');
        $this->check('Application', 'APP_KEY is configured', filled(config('app.key')), 'APP_KEY is present.');
        $this->check('Application', 'APP_DEBUG is disabled', config('app.debug') === false, 'APP_DEBUG=false is set.', required: $production);
        $this->check('Application', 'APP_URL uses HTTPS', str_starts_with((string) config('app.url'), 'https://'), 'APP_URL is HTTPS.', required: $production);
        $this->check('Runtime', 'PHP intl extension is installed', extension_loaded('intl'), 'PHP intl extension is loaded for Filament and Laravel number formatting.');
        $this->check('Database', 'Database connection works', $this->databaseIsReachable(), 'Database connection responded.');
        $this->check('Database', 'Migrations table exists', Schema::hasTable('migrations'), 'Migrations table exists.');
        $this->check('Queue', 'Queue driver is asynchronous', config('queue.default') !== 'sync', 'Queue driver is not sync.');
        $this->check('Queue', 'Failed job storage is database-backed', config('queue.failed.driver') === 'database-uuids', 'Failed jobs use database-uuids.');
        $this->check('Scheduler', 'Scheduler routes are configured', $this->schedulerLooksConfigured(), 'Core scheduler commands are registered.');
        $this->check('Storage', 'Storage directory is writable', is_writable(storage_path('framework')), 'Storage framework directory is writable.');
        $this->check('Storage', 'Public storage disk is configured', filled(config('filesystems.disks.public.root')) && filled(config('filesystems.disks.public.url')), 'Public storage disk has root and URL.');
        $this->check('Backups', 'Backup disk is configured', filled(config('deployment.backups.disk')), 'Backup disk is configured.', required: $production);
        $this->check('Backups', 'Backup retention is configured', (int) config('deployment.backups.retention_days') >= 7, 'Backup retention is at least 7 days.', required: $production);
        $this->check('Payments', 'Paystack keys are configured', filled(config('services.paystack.public_key')) && filled(config('services.paystack.secret_key')) && filled(config('services.paystack.webhook_secret')), 'Paystack public, secret, and webhook keys are configured.', required: $production);
        $this->check('Notifications', 'ZeptoMail is configured', filled(config('services.zeptomail.api_key')) && filled(config('services.zeptomail.from_address')), 'ZeptoMail API key and sender are configured.', required: $production);
        $this->check('Notifications', 'SES fallback is configured', filled(config('services.ses.key')) && filled(config('services.ses.secret')) && filled(config('services.ses.region')), 'SES credentials and region are configured.', required: $production);
        $this->check('Notifications', 'WhatsApp is configured', filled(config('services.whatsapp.phone_number_id')) && filled(config('services.whatsapp.access_token')), 'WhatsApp phone number ID and token are configured.', required: $production);
        $this->check('LMS', 'Active Moodle connection exists', $this->tableHasActiveRecord(MoodleConnection::class, 'moodle_connections'), 'At least one active Moodle connection exists.', required: $production);
        $this->check('Community', 'Active Discourse connection exists', $this->tableHasActiveRecord(DiscourseConnection::class, 'discourse_connections'), 'At least one active Discourse connection exists.', required: $production);
        if (config('scout.driver') === 'meilisearch') {
            $this->check('Search', 'Meilisearch is reachable', $this->meilisearchIsHealthy(), 'Meilisearch responded to a health check.', required: $production);
        }
        $this->check('Seed Data', 'Required roles are seeded', $this->rolesAreSeeded(), 'Base roles are present.');
        $this->check('Seed Data', 'Published products exist', $this->tableHasPublishedProducts(), 'At least one published product exists.');
        $this->check('Seed Data', 'Notification events exist', $this->tableHasRecords(NotificationEvent::class, 'notification_events'), 'Notification events are seeded.');

        if ((bool) $this->option('json')) {
            $this->line(json_encode([
                'status' => $this->hasFailures() ? 'failed' : 'passed',
                'production' => $production,
                'results' => $this->results,
            ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        } else {
            $this->renderTable();
        }

        return $this->hasFailures() ? self::FAILURE : self::SUCCESS;
    }

    private function check(string $area, string $name, bool $passed, string $successMessage, bool $required = true): void
    {
        $production = (bool) $this->option('production');
        $status = $passed ? 'pass' : (($production && $required) ? 'fail' : 'warn');

        $this->results[] = [
            'area' => $area,
            'name' => $name,
            'status' => $status,
            'message' => $passed ? $successMessage : 'Not ready. Review configuration, seed data, or infrastructure.',
        ];
    }

    private function renderTable(): void
    {
        $this->table(['Area', 'Check', 'Status', 'Message'], $this->results);

        if ($this->hasFailures()) {
            $this->error('Production readiness failed. Fix all failed checks before cutover.');

            return;
        }

        if (collect($this->results)->contains(fn (array $result) => $result['status'] === 'warn')) {
            $this->warn('Readiness completed with warnings. Run with --production in the target environment before cutover.');

            return;
        }

        $this->info('Deployment readiness checks passed.');
    }

    private function hasFailures(): bool
    {
        return collect($this->results)->contains(fn (array $result) => $result['status'] === 'fail');
    }

    private function databaseIsReachable(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function meilisearchIsHealthy(): bool
    {
        try {
            $host = rtrim((string) config('scout.meilisearch.host'), '/');

            if ($host === '') {
                return false;
            }

            $response = Http::timeout(3)->get($host.'/health');

            return $response->ok() && data_get($response->json(), 'status') === 'available';
        } catch (Throwable) {
            return false;
        }
    }

    private function schedulerLooksConfigured(): bool
    {
        $consoleRoutes = File::exists(base_path('routes/console.php'))
            ? File::get(base_path('routes/console.php'))
            : '';

        foreach ([
            'skillup:installment-reminders',
            'skillup:moodle-import',
            'skillup:moodle-reconcile',
            'queue:prune-failed',
        ] as $command) {
            if (! str_contains($consoleRoutes, $command)) {
                return false;
            }
        }

        return true;
    }

    private function rolesAreSeeded(): bool
    {
        if (! Schema::hasTable('roles')) {
            return false;
        }

        return Role::query()
            ->whereIn('name', ['Super Admin', 'Admin', 'Learner', 'Corporate', 'Instructor'])
            ->count() === 5;
    }

    private function tableHasPublishedProducts(): bool
    {
        return Schema::hasTable('products') && Product::published()->exists();
    }

    /**
     * @param  class-string  $model
     */
    private function tableHasActiveRecord(string $model, string $table): bool
    {
        return Schema::hasTable($table) && $model::query()->where('is_active', true)->exists();
    }

    /**
     * @param  class-string  $model
     */
    private function tableHasRecords(string $model, string $table): bool
    {
        return Schema::hasTable($table) && $model::query()->exists();
    }

    private function envTemplatesLookSafe(): bool
    {
        $unsafePatterns = [
            '/fc-[A-Za-z0-9]{20,}/',
            '/sk_(live|test)_[A-Za-z0-9]+/',
            '/pk_(live|test)_[A-Za-z0-9]+/',
            '/AKIA[0-9A-Z]{16}/',
            '/xox[baprs]-[A-Za-z0-9-]+/',
        ];

        foreach (['.env.example', '.env.production.example'] as $relativePath) {
            $path = base_path($relativePath);

            if (! File::exists($path)) {
                continue;
            }

            $contents = File::get($path);

            foreach ($unsafePatterns as $pattern) {
                if (preg_match($pattern, $contents) === 1) {
                    return false;
                }
            }
        }

        return true;
    }
}
