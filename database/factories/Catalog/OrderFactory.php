<?php

namespace Database\Factories\Catalog;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Catalog\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $total = fake()->randomElement([100000, 150000, 200000]);

        return [
            'uuid' => fake()->uuid(),
            'order_number' => 'SU-'.now()->format('Ymd').'-'.fake()->unique()->numberBetween(100000, 999999),
            'user_id' => User::factory(),
            'corporate_account_id' => null,
            'status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
            'currency' => 'NGN',
            'subtotal' => $total,
            'discount_total' => 0,
            'tax_total' => 0,
            'total' => $total,
            'amount_paid' => 0,
            'balance_due' => $total,
            'payment_provider' => 'paystack',
            'provider_reference' => null,
            'paid_at' => null,
            'metadata' => [],
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
            'amount_paid' => $attributes['total'],
            'balance_due' => 0,
            'paid_at' => now(),
        ]);
    }
}
