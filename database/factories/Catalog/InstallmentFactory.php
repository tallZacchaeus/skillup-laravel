<?php

namespace Database\Factories\Catalog;

use App\Enums\InstallmentStatus;
use App\Models\Catalog\Installment;
use App\Models\Catalog\Order;
use App\Models\Catalog\PaymentPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Installment>
 */
class InstallmentFactory extends Factory
{
    protected $model = Installment::class;

    public function definition(): array
    {
        return [
            'payment_plan_id' => PaymentPlan::factory(),
            'order_id' => Order::factory(),
            'installment_number' => 1,
            'status' => InstallmentStatus::Pending,
            'currency' => 'NGN',
            'amount' => 100000,
            'amount_paid' => 0,
            'due_at' => now(),
            'metadata' => [],
        ];
    }
}
