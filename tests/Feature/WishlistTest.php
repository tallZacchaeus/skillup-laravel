<?php

namespace Tests\Feature;

use App\Models\Catalog\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_toggle_wishlist(): void
    {
        $product = Product::factory()->published()->create(['cohort_id' => null]);

        $this->post(route('wishlist.toggle', $product->slug))->assertRedirect(route('login'));

        $this->assertDatabaseCount('wishlist_items', 0);
    }

    public function test_user_can_toggle_wishlist_on_and_off(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->published()->create(['cohort_id' => null]);

        $this->actingAs($user)->post(route('wishlist.toggle', $product->slug))->assertRedirect();
        $this->assertDatabaseHas('wishlist_items', ['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)->post(route('wishlist.toggle', $product->slug))->assertRedirect();
        $this->assertDatabaseMissing('wishlist_items', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_wishlist_index_lists_saved_courses(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->published()->create(['cohort_id' => null]);
        $user->wishlistProducts()->attach($product->id);

        $this->actingAs($user)
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Public/Wishlist')
                ->has('products', 1)
                ->where('products.0.slug', $product->slug));
    }

    public function test_wishlist_state_is_shared_with_inertia(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->published()->create(['cohort_id' => null]);
        $user->wishlistProducts()->attach($product->id);

        $this->actingAs($user)
            ->get(route('wishlist.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('wishlist.count', 1)
                ->where('wishlist.ids', [$product->id]));
    }
}
