<?php

namespace Tests\Feature\Deployment;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class Phase15DeploymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoints_support_deployment_smoke_checks(): void
    {
        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('a', 32)),
            'queue.default' => 'database',
        ]);

        $this->get(route('deployment.live'))
            ->assertOk()
            ->assertJsonPath('status', 'ok');

        $this->get(route('deployment.ready'))
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database.ok', true)
            ->assertJsonPath('checks.queue.ok', true);
    }

    public function test_deployment_commands_are_registered_and_safe_by_default(): void
    {
        $this->artisan('skillup:deployment-readiness --json')
            ->assertExitCode(extension_loaded('intl') ? 0 : 1);

        $this->artisan('skillup:deployment-readiness --production --json')
            ->assertExitCode(1);

        $this->artisan('skillup:smoke-test')
            ->assertExitCode(0);
    }

    public function test_scheduler_contains_phase_15_operational_jobs(): void
    {
        $consoleRoutes = File::get(base_path('routes/console.php'));

        foreach ([
            'skillup:installment-reminders',
            'skillup:moodle-import',
            'skillup:moodle-reconcile',
            'queue:prune-failed',
        ] as $scheduledCommand) {
            $this->assertStringContainsString($scheduledCommand, $consoleRoutes);
        }
    }

    public function test_phase_15_runbook_and_env_template_cover_cutover_requirements(): void
    {
        $runbook = File::get(base_path('docs/deployment/phase-15-deployment-cutover.md'));
        $env = File::get(base_path('.env.production.example'));

        foreach ([
            'Queue',
            'Scheduler',
            'Storage',
            'Backups',
            'Domain and SSL',
            'Paystack',
            'Moodle',
            'ZeptoMail',
            'SES',
            'WhatsApp',
            'Discourse',
            'Disable WooCommerce checkout',
            'Post-Launch Monitoring',
            'Rollback',
        ] as $requiredSection) {
            $this->assertStringContainsString($requiredSection, $runbook);
        }

        foreach ([
            'APP_ENV=production',
            'APP_DEBUG=false',
            'QUEUE_CONNECTION=database',
            'BACKUP_DISK=s3',
            'PAYSTACK_WEBHOOK_SECRET=',
            'ZEPTOMAIL_API_KEY=',
            'WHATSAPP_ACCESS_TOKEN=',
            'DISCOURSE_SSO_SECRET=',
            'LEGACY_WORDPRESS_URL=',
        ] as $requiredEnvLine) {
            $this->assertStringContainsString($requiredEnvLine, $env);
        }

        $this->assertDoesNotMatchRegularExpression('/fc-[A-Za-z0-9]{20,}|sk_(live|test)_[A-Za-z0-9]+|AKIA[0-9A-Z]{16}/', $env);
    }
}
