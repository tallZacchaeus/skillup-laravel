<?php

namespace Tests\Feature;

use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function enrolledUser(Product $product): User
    {
        $user = User::factory()->create();
        Enrollment::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        return $user;
    }

    public function test_enrolled_learner_can_post_a_verified_review(): void
    {
        $product = Product::factory()->published()->create(['cohort_id' => null]);
        $user = $this->enrolledUser($product);

        $this->actingAs($user)
            ->post(route('courses.reviews.store', $product->slug), [
                'rating' => 5,
                'title' => 'Excellent',
                'body' => 'Really solid, practical course content.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('product_reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'is_verified' => true,
            'is_published' => true,
        ]);

        $this->assertSame(1, $product->fresh()->rating_count);
        $this->assertSame(5.0, (float) $product->fresh()->rating_average);
    }

    public function test_non_enrolled_user_cannot_review(): void
    {
        $product = Product::factory()->published()->create(['cohort_id' => null]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('courses.reviews.store', $product->slug), [
                'rating' => 5,
                'body' => 'Trying to review without enrolling.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_guest_cannot_review(): void
    {
        $product = Product::factory()->published()->create(['cohort_id' => null]);

        $this->post(route('courses.reviews.store', $product->slug), [
            'rating' => 5,
            'body' => 'Guest review attempt should fail.',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_second_review_updates_instead_of_duplicating(): void
    {
        $product = Product::factory()->published()->create(['cohort_id' => null]);
        $user = $this->enrolledUser($product);

        $this->actingAs($user)->post(route('courses.reviews.store', $product->slug), [
            'rating' => 4,
            'body' => 'First impression was pretty good.',
        ])->assertRedirect();

        $this->actingAs($user)->post(route('courses.reviews.store', $product->slug), [
            'rating' => 2,
            'body' => 'Changed my mind after finishing it.',
        ])->assertRedirect();

        $this->assertDatabaseCount('product_reviews', 1);
        $this->assertSame(2, (int) $product->fresh()->reviews()->first()->rating);
        $this->assertSame(2.0, (float) $product->fresh()->rating_average);
    }

    public function test_review_requires_valid_rating_and_body(): void
    {
        $product = Product::factory()->published()->create(['cohort_id' => null]);
        $user = $this->enrolledUser($product);

        $this->actingAs($user)
            ->post(route('courses.reviews.store', $product->slug), ['rating' => 9, 'body' => 'short'])
            ->assertSessionHasErrors(['rating', 'body']);

        $this->assertDatabaseCount('product_reviews', 0);
    }
}
