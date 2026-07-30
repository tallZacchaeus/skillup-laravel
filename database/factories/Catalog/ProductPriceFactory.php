<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductPrice>
 */
class ProductPriceFactory extends Factory
{
    protected $model = ProductPrice::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'label' => 'Standard',
            'currency' => 'NGN',
            'amount' => fake()->randomElement([100000, 150000, 200000]),
            'compare_at_amount' => null,
            'is_default' => true,
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
            'metadata' => [],
        ];
    }
}
