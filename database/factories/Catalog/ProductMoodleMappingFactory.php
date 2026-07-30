<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductMoodleMapping;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductMoodleMapping>
 */
class ProductMoodleMappingFactory extends Factory
{
    protected $model = ProductMoodleMapping::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'moodle_course_id' => (string) fake()->unique()->numberBetween(1000, 9999),
            'moodle_category_id' => (string) fake()->numberBetween(100, 999),
            'moodle_group_id' => null,
            'moodle_cohort_id' => null,
            'is_primary' => true,
            'sync_enabled' => true,
            'last_synced_at' => null,
            'metadata' => [],
        ];
    }
}
