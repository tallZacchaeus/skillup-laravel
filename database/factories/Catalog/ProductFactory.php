<?php

namespace Database\Factories\Catalog;

use App\Enums\ProductStatus;
use App\Models\Catalog\Cohort;
use App\Models\Catalog\CourseLevel;
use App\Models\Catalog\Product;
use App\Models\Catalog\Track;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'uuid' => fake()->uuid(),
            'track_id' => Track::factory(),
            'course_level_id' => CourseLevel::factory(),
            'cohort_id' => Cohort::factory(),
            'title' => Str::title(trim($title, '.')),
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'subtitle' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'outcomes' => ['Build a portfolio project', 'Understand core workflows'],
            'syllabus' => [['week' => 1, 'title' => 'Foundations']],
            'requirements' => ['Laptop', 'Stable internet'],
            'status' => ProductStatus::Draft,
            'delivery_mode' => 'online',
            'enrollment_cap' => 40,
            'unlimited_enrollment' => false,
            'published_at' => null,
            'is_featured' => false,
            'sort_order' => 0,
            'metadata' => [],
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => ProductStatus::Published,
            'published_at' => now()->subMinute(),
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn () => [
            'status' => ProductStatus::Hidden,
            'published_at' => now()->subMinute(),
        ]);
    }
}
