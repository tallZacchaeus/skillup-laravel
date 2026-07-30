<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductMedia>
 */
class ProductMediaFactory extends Factory
{
    protected $model = ProductMedia::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'type' => 'image',
            'disk' => 'public',
            'path' => 'images/course-placeholder.jpg',
            'url' => null,
            'alt_text' => fake()->sentence(3),
            'is_primary' => true,
            'sort_order' => 0,
            'metadata' => [],
        ];
    }
}
