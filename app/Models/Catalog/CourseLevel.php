<?php

namespace App\Models\Catalog;

use Database\Factories\Catalog\CourseLevelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseLevel extends Model
{
    /** @use HasFactory<CourseLevelFactory> */
    use HasFactory;

    protected $fillable = [
        'track_id',
        'name',
        'slug',
        'rank',
        'status',
        'summary',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
