<?php

namespace Database\Factories\Catalog;

use App\Enums\PaymentPlanStatus;
use App\Models\Catalog\Order;
use App\Models\Catalog\PaymentPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentPlan>
 */
class PaymentPlanFactory extends Factory
{
    protected $model = PaymentPlan::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'name' => 'Two-part installment',
            'status' => PaymentPlanStatus::Active,
            'currency' => 'NGN',
            'total_amount' => 200000,
            'deposit_amount' => 100000,
            'installment_amount' => 100000,
            'installments_count' => 2,
            'interval' => 'monthly',
            'starts_at' => now(),
            'metadata' => [],
        ];
    }
}
