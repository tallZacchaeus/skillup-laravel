<?php

namespace Tests\Feature\Platform;

use App\Models\Platform\FutureModule;
use Database\Seeders\FutureModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FutureModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_future_modules_are_seeded_as_planned_and_hidden(): void
    {
        $this->seed(FutureModuleSeeder::class);

        $this->assertDatabaseHas('future_modules', [
            'key' => 'school_youth_program',
            'status' => 'planned',
            'is_publicly_visible' => false,
        ]);
        $this->assertDatabaseHas('future_modules', [
            'key' => 'certificate_builder_verification',
            'status' => 'planned',
            'is_publicly_visible' => false,
        ]);
        $this->assertSame(9, FutureModule::count());
    }

    public function test_public_future_module_route_is_not_available_until_active(): void
    {
        $this->seed(FutureModuleSeeder::class);

        $this->get('/schools')->assertNotFound();

        // /certificates/verify is no longer a future-module placeholder — it is
        // a real page shipped with the Programs module (Phase 2).
        $this->get('/certificates/verify')->assertOk();
    }

    public function test_active_future_module_can_render_public_readiness_page(): void
    {
        $this->seed(FutureModuleSeeder::class);

        FutureModule::where('key', 'career_center')->update([
            'status' => 'active',
            'is_publicly_visible' => true,
        ]);

        $this->get('/career-center')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/FutureModules/Show')
                ->where('module.key', 'career_center')
                ->where('module.name', 'Career Center'));
    }
}
