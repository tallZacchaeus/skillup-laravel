<?php

namespace Database\Factories\Catalog;

use App\Enums\PaymentStatus;
use App\Models\Catalog\Order;
use App\Models\Catalog\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'provider' => 'paystack',
            'reference' => 'PAY-'.Str::upper(fake()->unique()->bothify('????????####')),
            'status' => PaymentStatus::Pending,
            'currency' => 'NGN',
            'amount' => 200000,
            'initialized_at' => now(),
            'metadata' => [],
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'verified_at' => now(),
        ]);
    }
}
