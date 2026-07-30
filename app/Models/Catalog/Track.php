<?php

namespace App\Models\Catalog;

use App\Enums\ProductStatus;
use Database\Factories\Catalog\TrackFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    /** @use HasFactory<TrackFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'phase',
        'status',
        'summary',
        'description',
        'image_path',
        'outcomes',
        'tools',
        'is_featured',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'outcomes' => 'array',
            'tools' => 'array',
            'is_featured' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Published->value);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(CourseLevel::class)->orderBy('rank');
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
