<?php

namespace Database\Factories\Catalog;

use App\Enums\InvoiceStatus;
use App\Models\Catalog\Invoice;
use App\Models\Catalog\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'status' => InvoiceStatus::Issued,
            'currency' => 'NGN',
            'subtotal' => 200000,
            'discount_total' => 0,
            'tax_total' => 0,
            'total' => 200000,
            'issued_at' => now(),
            'due_at' => now()->addDays(7),
            'metadata' => [],
        ];
    }
}
