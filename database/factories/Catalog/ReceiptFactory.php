<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Order;
use App\Models\Catalog\Payment;
use App\Models\Catalog\Receipt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receipt>
 */
class ReceiptFactory extends Factory
{
    protected $model = Receipt::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_id' => Payment::factory(),
            'currency' => 'NGN',
            'amount' => 200000,
            'issued_at' => now(),
            'metadata' => [],
        ];
    }
}
