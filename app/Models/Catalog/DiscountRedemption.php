<?php

namespace App\Models\Catalog;

use App\Enums\DiscountRedemptionStatus;
use App\Enums\DiscountType;
use App\Models\User;
use Database\Factories\Catalog\DiscountRedemptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DiscountRedemption extends Model
{
    /** @use HasFactory<DiscountRedemptionFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'discount_rule_id',
        'discount_code_id',
        'discount_eligibility_list_id',
        'user_id',
        'order_id',
        'product_id',
        'email',
        'normalized_email',
        'status',
        'discount_type',
        'discount_value',
        'currency',
        'subtotal',
        'discount_amount',
        'total_after_discount',
        'code',
        'snapshot',
        'locked_at',
        'redeemed_at',
        'released_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => DiscountRedemptionStatus::class,
            'discount_type' => DiscountType::class,
            'discount_value' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_after_discount' => 'decimal:2',
            'snapshot' => 'array',
            'locked_at' => 'datetime',
            'redeemed_at' => 'datetime',
            'released_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DiscountRedemption $redemption) {
            $redemption->uuid ??= (string) Str::uuid();
            $redemption->locked_at ??= now();
        });

        static::saving(function (DiscountRedemption $redemption) {
            $redemption->normalized_email = DiscountEligibleEmail::normalizeEmail($redemption->email);
        });
    }

    public function discountRule(): BelongsTo
    {
        return $this->belongsTo(DiscountRule::class);
    }

    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class);
    }

    public function eligibilityList(): BelongsTo
    {
        return $this->belongsTo(DiscountEligibilityList::class, 'discount_eligibility_list_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
