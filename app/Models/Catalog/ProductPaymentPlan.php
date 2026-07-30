<?php

namespace App\Models\Catalog;

use Database\Factories\Catalog\ProductPaymentPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPaymentPlan extends Model
{
    /** @use HasFactory<ProductPaymentPlanFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'slug',
        'description',
        'currency',
        'deposit_amount',
        'installment_amount',
        'installments_count',
        'interval',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'deposit_amount' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
