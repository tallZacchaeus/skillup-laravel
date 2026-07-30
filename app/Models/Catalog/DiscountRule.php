<?php

namespace App\Models\Catalog;

use App\Enums\DiscountRuleStatus;
use App\Enums\DiscountType;
use App\Models\User;
use Database\Factories\Catalog\DiscountRuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DiscountRule extends Model
{
    /** @use HasFactory<DiscountRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'created_by_user_id',
        'track_id',
        'product_id',
        'course_level_id',
        'cohort_id',
        'name',
        'slug',
        'description',
        'status',
        'type',
        'value',
        'currency',
        'minimum_order_amount',
        'starts_at',
        'ends_at',
        'usage_limit',
        'per_email_limit',
        'per_user_limit',
        'requires_code',
        'requires_email_eligibility',
        'installment_compatible',
        'stackable',
        'is_public',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => DiscountRuleStatus::class,
            'type' => DiscountType::class,
            'value' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'requires_code' => 'boolean',
            'requires_email_eligibility' => 'boolean',
            'installment_compatible' => 'boolean',
            'stackable' => 'boolean',
            'is_public' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DiscountRule $rule) {
            $rule->uuid ??= (string) Str::uuid();
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', DiscountRuleStatus::Active->value)
            ->where(function (Builder $query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(CourseLevel::class, 'course_level_id');
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function codes(): HasMany
    {
        return $this->hasMany(DiscountCode::class);
    }

    public function eligibilityLists(): HasMany
    {
        return $this->hasMany(DiscountEligibilityList::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }
}
