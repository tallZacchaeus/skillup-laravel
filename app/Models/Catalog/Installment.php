<?php

namespace App\Models\Catalog;

use App\Enums\InstallmentStatus;
use Database\Factories\Catalog\InstallmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installment extends Model
{
    /** @use HasFactory<InstallmentFactory> */
    use HasFactory;

    protected $fillable = [
        'payment_plan_id',
        'order_id',
        'installment_number',
        'status',
        'currency',
        'amount',
        'amount_paid',
        'due_at',
        'paid_at',
        'reminder_sent_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => InstallmentStatus::class,
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function scopeDueForReminder(Builder $query): Builder
    {
        return $query
            ->where('status', InstallmentStatus::Pending->value)
            ->whereNotNull('due_at')
            ->where('due_at', '<=', now()->addDays(3))
            ->whereNull('reminder_sent_at');
    }

    public function paymentPlan(): BelongsTo
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
