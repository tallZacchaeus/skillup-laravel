<?php

namespace App\Models\Catalog;

use App\Enums\InvoiceStatus;
use App\Models\User;
use Database\Factories\Catalog\InvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'order_id',
        'user_id',
        'invoice_number',
        'status',
        'currency',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
        'issued_at',
        'due_at',
        'paid_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            $invoice->uuid ??= (string) Str::uuid();
            $invoice->invoice_number ??= 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
            $invoice->issued_at ??= now();
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
}
