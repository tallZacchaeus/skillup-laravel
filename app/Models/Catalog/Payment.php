<?php

namespace App\Models\Catalog;

use App\Enums\PaymentStatus;
use App\Models\User;
use Database\Factories\Catalog\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'order_id',
        'user_id',
        'provider',
        'reference',
        'provider_transaction_id',
        'access_code',
        'authorization_url',
        'status',
        'currency',
        'amount',
        'channel',
        'gateway_response',
        'initialized_at',
        'paid_at',
        'verified_at',
        'failed_at',
        'provider_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'initialized_at' => 'datetime',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
            'failed_at' => 'datetime',
            'provider_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            $payment->uuid ??= (string) Str::uuid();
            $payment->reference ??= 'PAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(12));
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class);
    }
}
