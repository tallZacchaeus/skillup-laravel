<?php

namespace App\Models\Catalog;

use App\Enums\ProductStatus;
use App\Models\Programs\ProgramEditionTrack;
use Database\Factories\Catalog\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, Searchable;

    protected $fillable = [
        'uuid',
        'track_id',
        'course_level_id',
        'cohort_id',
        'title',
        'slug',
        'subtitle',
        'description',
        'promo_video_url',
        'outcomes',
        'syllabus',
        'requirements',
        'relevance',
        'status',
        'delivery_mode',
        'enrollment_cap',
        'unlimited_enrollment',
        'published_at',
        'is_featured',
        'rating_average',
        'rating_count',
        'students_count',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'outcomes' => 'array',
            'syllabus' => 'array',
            'requirements' => 'array',
            'relevance' => 'array',
            'unlimited_enrollment' => 'boolean',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'rating_average' => 'float',
            'rating_count' => 'integer',
            'students_count' => 'integer',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            $product->uuid ??= (string) Str::uuid();
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ProductStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * @return array<int, string>
     */
    public function missingPublicationFields(): array
    {
        $missing = [];

        foreach ([
            'title' => 'title',
            'slug' => 'slug',
            'track_id' => 'track',
            'course_level_id' => 'level',
            'description' => 'description',
            'outcomes' => 'outcomes',
            'syllabus' => 'syllabus',
        ] as $attribute => $label) {
            if (blank($this->{$attribute})) {
                $missing[] = $label;
            }
        }

        if (! $this->unlimited_enrollment && blank($this->enrollment_cap)) {
            $missing[] = 'enrollment cap';
        }

        if (! $this->defaultPrice()->where('is_active', true)->exists()) {
            $missing[] = 'active default price';
        }

        return $missing;
    }

    public function canBePublished(): bool
    {
        return $this->missingPublicationFields() === [];
    }

    public function publish(): bool
    {
        if (! $this->canBePublished()) {
            return false;
        }

        return $this->forceFill([
            'status' => ProductStatus::Published,
            'published_at' => $this->published_at ?? now(),
        ])->save();
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(CourseLevel::class, 'course_level_id');
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function defaultPrice(): HasOne
    {
        return $this->hasOne(ProductPrice::class)->where('is_default', true);
    }

    public function paymentPlans(): HasMany
    {
        return $this->hasMany(ProductPaymentPlan::class);
    }

    public function visibilityRules(): HasMany
    {
        return $this->hasMany(ProductVisibilityRule::class);
    }

    public function moodleMappings(): HasMany
    {
        return $this->hasMany(ProductMoodleMapping::class);
    }

    public function primaryMoodleMapping(): HasOne
    {
        return $this->hasOne(ProductMoodleMapping::class)->where('is_primary', true);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function programEditionTracks(): HasMany
    {
        return $this->hasMany(ProgramEditionTrack::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    // ---- Laravel Scout (Meilisearch) ----

    public function shouldBeSearchable(): bool
    {
        return $this->status === ProductStatus::Published
            && $this->published_at !== null
            && $this->published_at <= now();
    }

    /**
     * Eager-load relations once for the whole import batch (avoids N+1).
     */
    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with(['track', 'level', 'defaultPrice', 'tags', 'programEditionTracks.edition.program']);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing(['track', 'level', 'defaultPrice', 'tags', 'programEditionTracks.edition.program']);

        $program = $this->programEditionTracks->first()?->edition?->program;

        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => strip_tags((string) $this->description),
            'track_title' => $this->track?->title,
            'track_slug' => $this->track?->slug,
            'level' => $this->level?->name,
            'delivery_mode' => $this->delivery_mode,
            'tags' => $this->tags->pluck('name')->values()->all(),
            'tools' => array_values($this->track?->tools ?? []),
            'price_amount' => (float) ($this->defaultPrice?->amount ?? 0),
            'currency' => $this->defaultPrice?->currency,
            'is_program' => $program !== null,
            'program_slug' => $program?->slug,
            'program_name' => $program?->name,
            'is_featured' => (bool) $this->is_featured,
            'rating_average' => (float) $this->rating_average,
            'students_count' => (int) $this->students_count,
            'published_at' => $this->published_at?->getTimestamp(),
            'sort_order' => (int) $this->sort_order,
        ];
    }

    public function publishedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)
            ->where('is_published', true)
            ->orderByDesc('reviewed_at');
    }

    /** Recompute the cached rating aggregates from published reviews. */
    public function recalculateRating(): void
    {
        $aggregate = $this->reviews()
            ->where('is_published', true)
            ->selectRaw('COUNT(*) as count, AVG(rating) as average')
            ->first();

        $count = (int) ($aggregate->count ?? 0);

        $this->forceFill([
            'rating_count' => $count,
            'rating_average' => $count > 0 ? round((float) $aggregate->average, 2) : 0,
        ])->saveQuietly();
    }
}
