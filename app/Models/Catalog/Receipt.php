<?php

namespace App\Models\Catalog;

use Database\Factories\Catalog\ReceiptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Receipt extends Model
{
    /** @use HasFactory<ReceiptFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'order_id',
        'payment_id',
        'receipt_number',
        'currency',
        'amount',
        'issued_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'issued_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Receipt $receipt) {
            $receipt->uuid ??= (string) Str::uuid();
            $receipt->receipt_number ??= 'RCT-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
            $receipt->issued_at ??= now();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
