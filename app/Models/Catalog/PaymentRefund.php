<?php

namespace App\Models\Catalog;

use App\Enums\RefundStatus;
use App\Models\User;
use Database\Factories\Catalog\PaymentRefundFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PaymentRefund extends Model
{
    /** @use HasFactory<PaymentRefundFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'payment_id',
        'order_id',
        'requested_by_user_id',
        'provider',
        'reference',
        'provider_refund_id',
        'status',
        'currency',
        'amount',
        'reason',
        'requested_at',
        'processed_at',
        'provider_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => RefundStatus::class,
            'amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
            'provider_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PaymentRefund $refund) {
            $refund->uuid ??= (string) Str::uuid();
            $refund->reference ??= 'REF-'.now()->format('Ymd').'-'.Str::upper(Str::random(10));
            $refund->requested_at ??= now();
        });
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
