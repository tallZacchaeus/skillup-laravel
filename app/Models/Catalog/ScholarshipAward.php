<?php

namespace App\Models\Catalog;

use App\Enums\DiscountType;
use App\Enums\ScholarshipAwardStatus;
use App\Models\User;
use Database\Factories\Catalog\ScholarshipAwardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ScholarshipAward extends Model
{
    /** @use HasFactory<ScholarshipAwardFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'scholarship_application_id',
        'discount_rule_id',
        'user_id',
        'awarded_by_user_id',
        'track_id',
        'product_id',
        'cohort_id',
        'email',
        'normalized_email',
        'status',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
        'awarded_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ScholarshipAwardStatus::class,
            'discount_type' => DiscountType::class,
            'discount_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'awarded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ScholarshipAward $award) {
            $award->uuid ??= (string) Str::uuid();
            $award->awarded_at ??= now();
        });

        static::saving(function (ScholarshipAward $award) {
            $award->normalized_email = DiscountEligibleEmail::normalizeEmail($award->email);
            $award->email = $award->normalized_email;
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(ScholarshipApplication::class, 'scholarship_application_id');
    }

    public function discountRule(): BelongsTo
    {
        return $this->belongsTo(DiscountRule::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function awarder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by_user_id');
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }
}
