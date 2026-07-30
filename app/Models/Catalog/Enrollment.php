<?php

namespace App\Models\Catalog;

use App\Enums\EnrollmentStatus;
use App\Models\User;
use Database\Factories\Catalog\EnrollmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'product_id',
        'cohort_id',
        'order_id',
        'order_item_id',
        'corporate_account_id',
        'status',
        'access_starts_at',
        'access_ends_at',
        'moodle_user_id',
        'moodle_course_id',
        'moodle_enrollment_id',
        'provisioned_at',
        'failed_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'access_starts_at' => 'datetime',
            'access_ends_at' => 'datetime',
            'provisioned_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Enrollment $enrollment) {
            $enrollment->uuid ??= (string) Str::uuid();
        });

        static::created(function (Enrollment $enrollment) {
            if ($enrollment->user) {
                \App\Jobs\Discourse\SyncUserDiscourseGroupsJob::dispatch($enrollment->user);
            }
        });

        static::updated(function (Enrollment $enrollment) {
            if ($enrollment->isDirty('status') && $enrollment->user) {
                \App\Jobs\Discourse\SyncUserDiscourseGroupsJob::dispatch($enrollment->user);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function corporateAccount(): BelongsTo
    {
        return $this->belongsTo(CorporateAccount::class);
    }
}
