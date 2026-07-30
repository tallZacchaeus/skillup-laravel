<?php

namespace App\Models\Catalog;

use App\Enums\PaymentPlanStatus;
use Database\Factories\Catalog\PaymentPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PaymentPlan extends Model
{
    /** @use HasFactory<PaymentPlanFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'order_id',
        'product_payment_plan_id',
        'name',
        'status',
        'currency',
        'total_amount',
        'deposit_amount',
        'installment_amount',
        'installments_count',
        'interval',
        'starts_at',
        'completed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentPlanStatus::class,
            'total_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PaymentPlan $plan) {
            $plan->uuid ??= (string) Str::uuid();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function productPaymentPlan(): BelongsTo
    {
        return $this->belongsTo(ProductPaymentPlan::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }
}
