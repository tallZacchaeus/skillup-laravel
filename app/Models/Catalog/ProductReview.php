<?php

namespace App\Models\Catalog;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductReview extends Model
{
    protected $fillable = [
        'uuid',
        'product_id',
        'user_id',
        'reviewer_name',
        'reviewer_title',
        'rating',
        'title',
        'body',
        'is_verified',
        'is_published',
        'reviewed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_verified' => 'boolean',
            'is_published' => 'boolean',
            'reviewed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ProductReview $review) {
            $review->uuid ??= (string) Str::uuid();
        });

        // Keep the product's cached rating aggregates in sync.
        static::saved(fn (ProductReview $review) => $review->product?->recalculateRating());
        static::deleted(fn (ProductReview $review) => $review->product?->recalculateRating());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
