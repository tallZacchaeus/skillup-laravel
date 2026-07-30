<?php

namespace Database\Factories\Catalog;

use App\Enums\RefundStatus;
use App\Models\Catalog\Order;
use App\Models\Catalog\Payment;
use App\Models\Catalog\PaymentRefund;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaymentRefund>
 */
class PaymentRefundFactory extends Factory
{
    protected $model = PaymentRefund::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'order_id' => Order::factory(),
            'provider' => 'paystack',
            'reference' => 'REF-'.Str::upper(fake()->unique()->bothify('????????####')),
            'status' => RefundStatus::Pending,
            'currency' => 'NGN',
            'amount' => 200000,
            'reason' => fake()->sentence(),
            'requested_at' => now(),
            'metadata' => [],
        ];
    }
}
