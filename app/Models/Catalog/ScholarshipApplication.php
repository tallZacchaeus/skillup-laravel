<?php

namespace App\Models\Catalog;

use App\Enums\DiscountType;
use App\Enums\ScholarshipApplicationStatus;
use App\Models\User;
use Database\Factories\Catalog\ScholarshipApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ScholarshipApplication extends Model
{
    /** @use HasFactory<ScholarshipApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'track_id',
        'product_id',
        'cohort_id',
        'reviewed_by_user_id',
        'name',
        'email',
        'normalized_email',
        'phone',
        'country',
        'reason',
        'status',
        'requested_discount_type',
        'requested_discount_value',
        'reviewed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ScholarshipApplicationStatus::class,
            'requested_discount_type' => DiscountType::class,
            'requested_discount_value' => 'decimal:2',
            'reviewed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ScholarshipApplication $application) {
            $application->uuid ??= (string) Str::uuid();
        });

        static::saving(function (ScholarshipApplication $application) {
            $application->normalized_email = DiscountEligibleEmail::normalizeEmail($application->email);
            $application->email = $application->normalized_email;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
