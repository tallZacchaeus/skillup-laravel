<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Order;
use App\Models\Catalog\OrderItem;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $amount = fake()->randomElement([100000, 150000, 200000]);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_price_id' => ProductPrice::factory(),
            'product_title' => fake()->sentence(3),
            'quantity' => 1,
            'unit_amount' => $amount,
            'discount_amount' => 0,
            'total' => $amount,
            'metadata' => [],
        ];
    }
}
