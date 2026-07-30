<?php

namespace App\Models\Catalog;

use Database\Factories\Catalog\DiscountCodeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DiscountCode extends Model
{
    /** @use HasFactory<DiscountCodeFactory> */
    use HasFactory;

    protected $fillable = [
        'discount_rule_id',
        'code',
        'visibility',
        'max_redemptions',
        'redeemed_count',
        'starts_at',
        'ends_at',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (DiscountCode $code) {
            $code->code = Str::upper(trim((string) $code->code));
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function discountRule(): BelongsTo
    {
        return $this->belongsTo(DiscountRule::class);
    }
}
