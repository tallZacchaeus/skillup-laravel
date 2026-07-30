<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPaymentPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductPaymentPlan>
 */
class ProductPaymentPlanFactory extends Factory
{
    protected $model = ProductPaymentPlan::class;

    public function definition(): array
    {
        $name = 'Two-part installment';

        return [
            'product_id' => Product::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Deposit plus one installment.',
            'currency' => 'NGN',
            'deposit_amount' => 100000,
            'installment_amount' => 100000,
            'installments_count' => 2,
            'interval' => 'monthly',
            'is_active' => true,
            'metadata' => [],
        ];
    }
}
