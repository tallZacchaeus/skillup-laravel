<?php

namespace Tests\Feature;

use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_renders_enrolled_courses_and_metrics(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->published()->create();
        Enrollment::factory()->active()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Dashboard')
                ->has('courses', 1, fn ($c) => $c
                    ->where('accessible', true)
                    ->where('statusLabel', 'Active')
                    ->where('pendingReason', null)
                    ->etc())
                ->where('metrics.activeCourses', 1)
                ->has('certificates', 0)
                ->has('events', 0)
                ->has('notifications', 0));
    }

    public function test_pending_enrolment_is_not_accessible_and_explains_why(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->published()->create();
        Enrollment::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]); // default Pending

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('courses', 1, fn ($c) => $c
                    ->where('accessible', false)
                    ->where('statusLabel', 'Pending')
                    ->whereNot('pendingReason', null)
                    ->etc()));
    }

    public function test_recommendations_exclude_enrolled_courses(): void
    {
        $user = User::factory()->create();
        $enrolled = Product::factory()->published()->create();
        Product::factory()->published()->count(2)->create();
        Enrollment::factory()->active()->create(['user_id' => $user->id, 'product_id' => $enrolled->id]);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertInertia(function ($page) use ($enrolled) {
                $recIds = collect($page->toArray()['props']['recommendations'])->pluck('id');
                $this->assertFalse($recIds->contains($enrolled->id), 'Recommendations must not include enrolled courses.');
                $this->assertGreaterThanOrEqual(2, $recIds->count());
            });
    }
}
