<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVisibilityRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVisibilityRule>
 */
class ProductVisibilityRuleFactory extends Factory
{
    protected $model = ProductVisibilityRule::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'rule_type' => 'public',
            'operator' => 'equals',
            'value' => ['public' => true],
            'starts_at' => null,
            'ends_at' => null,
            'is_active' => true,
            'metadata' => [],
        ];
    }
}
