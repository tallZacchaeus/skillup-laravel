<?php

namespace App\Models\Catalog;

use App\Enums\CohortStatus;
use Database\Factories\Catalog\CohortFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cohort extends Model
{
    /** @use HasFactory<CohortFactory> */
    use HasFactory;

    protected $fillable = [
        'track_id',
        'course_level_id',
        'instructor_profile_id',
        'title',
        'slug',
        'status',
        'delivery_mode',
        'timezone',
        'starts_at',
        'ends_at',
        'enrollment_opens_at',
        'enrollment_closes_at',
        'max_learners',
        'enrolled_count',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => CohortStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'enrollment_opens_at' => 'datetime',
            'enrollment_closes_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', CohortStatus::Open->value);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(CourseLevel::class, 'course_level_id');
    }

    public function instructorProfile(): BelongsTo
    {
        return $this->belongsTo(InstructorProfile::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CohortSession::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
