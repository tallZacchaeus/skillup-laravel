<?php

namespace App\Support\Catalog;

use App\Models\Catalog\Cohort;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductMedia;
use App\Models\Catalog\ProductPrice;
use App\Models\Catalog\Track;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Formats catalog Tracks and Products for public Inertia pages.
 *
 * Shared by the home page and the courses catalogue so a "course card"
 * looks and links the same everywhere. Program-backed products can be
 * given a program context so their card routes into the /programs funnel
 * (guardian/safeguarding flow) instead of the generic catalogue checkout.
 */
class CatalogPresenter
{
    /**
     * Eager-load relations needed to format a product.
     *
     * @return array<int|string, mixed>
     */
    public function productRelations(): array
    {
        return [
            'track',
            'level',
            'cohort',
            'media',
            'tags',
            'defaultPrice' => fn ($query) => $query->active(),
            'paymentPlans' => fn ($query) => $query->where('is_active', true)->orderBy('deposit_amount'),
            'primaryMoodleMapping',
        ];
    }

    /**
     * @param  array<string, mixed>  $context  Optional: ['program' => ['slug' => ..., 'name' => ...]]
     * @return array<string, mixed>
     */
    public function formatProduct(Product $product, array $context = []): array
    {
        $media = $product->relationLoaded('media')
            ? ($product->media->firstWhere('is_primary', true) ?? $product->media->first())
            : null;
        $price = $product->defaultPrice;
        $cohort = $product->cohort;
        $program = $context['program'] ?? null;
        $reviews = $product->relationLoaded('publishedReviews') ? $product->publishedReviews : collect();

        $catalogUrl = $product->track
            ? route('courses.products.show', [$product->track->slug, $product->slug])
            : route('courses.index');

        return [
            'id' => $product->id,
            'uuid' => $product->uuid,
            'title' => $product->title,
            'slug' => $product->slug,
            'subtitle' => $product->subtitle,
            'summary' => $product->subtitle ?: Str::limit((string) $product->description, 150),
            'description' => $product->description,
            'trackSlug' => $product->track?->slug,
            'trackTitle' => $product->track?->title,
            'level' => $product->level?->name ?? 'Self-paced',
            'category' => $program
                ? 'Program'
                : ($product->track ? $this->trackCategory($product->track->phase) : 'Course'),
            'deliveryMode' => Str::headline((string) $product->delivery_mode),
            'duration' => $this->durationFromCohort($cohort),
            'price' => $price ? $this->money($price) : 'Waitlist',
            'amount' => $price?->amount,
            'currency' => $price?->currency,
            'image' => $this->mediaUrl($media, $product->track),
            'outcomes' => $product->outcomes ?? [],
            'syllabus' => $product->syllabus ?? [],
            'requirements' => $product->requirements ?? [],
            'tools' => $product->track?->tools ?? [],
            'tags' => $product->relationLoaded('tags')
                ? $product->tags->map(fn ($tag) => ['name' => $tag->name, 'slug' => $tag->slug])->values()
                : [],
            'seats' => $this->seatsLabel($product, $cohort),
            'promoVideo' => $this->videoEmbed($product->promo_video_url),
            'relevance' => $product->relevance,
            'studentsCount' => (int) $product->students_count,
            'studentsLabel' => $this->compactNumber((int) $product->students_count),
            'rating' => [
                'average' => round((float) $product->rating_average, 1),
                'count' => (int) $product->rating_count,
            ],
            'reviews' => $reviews->map(fn ($review) => [
                'id' => $review->id,
                'name' => $review->reviewer_name,
                'title' => $review->reviewer_title,
                'rating' => (int) $review->rating,
                'heading' => $review->title,
                'body' => $review->body,
                'verified' => (bool) $review->is_verified,
                'date' => $review->reviewed_at?->toFormattedDateString(),
            ])->values(),
            'reviewsSummary' => $reviews->isNotEmpty() ? [
                'average' => round((float) $product->rating_average, 1),
                'count' => (int) $product->rating_count,
                'distribution' => collect(range(5, 1))->mapWithKeys(fn ($star) => [
                    $star => $reviews->where('rating', $star)->count(),
                ])->all(),
            ] : null,
            'isProgram' => (bool) $program,
            'program' => $program,
            // Program products route to the program funnel; regular courses to the catalogue detail.
            'url' => $program ? route('programs.show', $program['slug']) : $catalogUrl,
            'trackUrl' => $program
                ? route('programs.show', $program['slug'])
                : ($product->track ? route('courses.show', $product->track->slug) : route('courses.index')),
            'cta' => $program ? 'Register' : 'View course',
            'cohort' => $cohort ? [
                'title' => $cohort->title,
                'status' => Str::headline($cohort->status->value),
                'startsAt' => $cohort->starts_at?->toFormattedDateString(),
                'enrollmentClosesAt' => $cohort->enrollment_closes_at?->toFormattedDateString(),
            ] : null,
            'paymentPlans' => $product->paymentPlans->map(fn ($plan) => [
                'name' => $plan->name,
                'description' => $plan->description,
                'deposit' => $this->moneyAmount($plan->currency, $plan->deposit_amount),
                'installment' => $this->moneyAmount($plan->currency, $plan->installment_amount),
                'installmentsCount' => $plan->installments_count,
                'interval' => Str::headline($plan->interval),
            ])->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function formatTrack(Track $track): array
    {
        $products = $track->relationLoaded('products') ? $track->products : collect();
        $levels = $products->isNotEmpty()
            ? $products->pluck('level.name')->filter()->unique()->values()
            : ($track->relationLoaded('levels') ? $track->levels->pluck('name')->filter()->values() : collect());

        return [
            'id' => $track->id,
            'slug' => $track->slug,
            'title' => $track->title,
            'category' => $this->trackCategory($track->phase),
            'level' => $this->levelLabel($levels),
            'duration' => $this->trackDuration($products),
            'price' => $this->trackPrice($products),
            'image' => $this->mediaUrl(null, $track),
            'summary' => $track->summary,
            'description' => $track->description,
            'outcomes' => $track->outcomes ?? [],
            'tools' => $track->tools ?? [],
            'products' => $products->map(fn (Product $product) => $this->formatProduct($product))->values(),
        ];
    }

    /**
     * Build schema.org JSON-LD for a course detail page (Course + BreadcrumbList).
     * Powers Google rich results — star ratings, price, and provider in search.
     *
     * @return array<int, array<string, mixed>>
     */
    public function courseStructuredData(Product $product): array
    {
        $product->loadMissing(['track', 'level', 'cohort', 'media', 'defaultPrice', 'publishedReviews']);

        $url = $product->track
            ? route('courses.products.show', [$product->track->slug, $product->slug])
            : route('courses.index');

        $media = $product->relationLoaded('media')
            ? ($product->media->firstWhere('is_primary', true) ?? $product->media->first())
            : null;
        $image = $this->mediaUrl($media, $product->track);
        $image = Str::startsWith($image, ['http://', 'https://']) ? $image : url($image);

        $course = [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $product->title,
            'description' => Str::limit(strip_tags((string) ($product->description ?: $product->subtitle)), 300),
            'url' => $url,
            'image' => $image,
            'provider' => [
                '@type' => 'Organization',
                'name' => config('app.name', 'SkillUp'),
                'sameAs' => url('/'),
            ],
        ];

        if ($price = $product->defaultPrice) {
            $course['offers'] = [
                '@type' => 'Offer',
                'category' => 'Paid',
                'price' => (string) (float) $price->amount,
                'priceCurrency' => $price->currency,
                'availability' => 'https://schema.org/InStock',
                'url' => $url,
            ];
        }

        if ((int) $product->rating_count > 0) {
            $course['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round((float) $product->rating_average, 1),
                'reviewCount' => (int) $product->rating_count,
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }

        $onsite = Str::contains(Str::lower((string) $product->delivery_mode), ['person', 'onsite', 'hybrid', 'physical']);
        $instance = ['@type' => 'CourseInstance', 'courseMode' => $onsite ? 'Onsite' : 'Online'];
        if ($product->cohort?->starts_at) {
            $instance['startDate'] = $product->cohort->starts_at->toDateString();
        }
        if ($product->cohort?->ends_at) {
            $instance['endDate'] = $product->cohort->ends_at->toDateString();
        }
        $course['hasCourseInstance'] = $instance;

        $reviews = $product->relationLoaded('publishedReviews') ? $product->publishedReviews : collect();
        if ($reviews->isNotEmpty()) {
            $course['review'] = $reviews->take(5)->map(fn ($r) => [
                '@type' => 'Review',
                'author' => ['@type' => 'Person', 'name' => $r->reviewer_name],
                'datePublished' => $r->reviewed_at?->toDateString(),
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => (int) $r->rating, 'bestRating' => 5, 'worstRating' => 1],
                'name' => $r->title,
                'reviewBody' => $r->body,
            ])->values()->all();
        }

        $breadcrumb = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_filter([
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Courses', 'item' => route('courses.index')],
                $product->track
                    ? ['@type' => 'ListItem', 'position' => 2, 'name' => $product->track->title, 'item' => route('courses.show', $product->track->slug)]
                    : null,
                ['@type' => 'ListItem', 'position' => $product->track ? 3 : 2, 'name' => $product->title, 'item' => $url],
            ])),
        ];

        return [$course, $breadcrumb];
    }

    /**
     * Normalise a promo video URL into an embeddable form.
     *
     * @return array{url: string, embedUrl: string, provider: string}|null
     */
    public function videoEmbed(?string $url): ?array
    {
        if (blank($url)) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{6,})~', $url, $m)) {
            return ['url' => $url, 'embedUrl' => 'https://www.youtube.com/embed/'.$m[1], 'provider' => 'youtube'];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return ['url' => $url, 'embedUrl' => 'https://player.vimeo.com/video/'.$m[1], 'provider' => 'vimeo'];
        }

        return ['url' => $url, 'embedUrl' => $url, 'provider' => 'file'];
    }

    /** 1284 -> "1,284"; 12500 -> "12.5k". */
    public function compactNumber(int $value): string
    {
        if ($value >= 1000) {
            return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.').'k';
        }

        return (string) $value;
    }

    public function mediaUrl(?ProductMedia $media, ?Track $track): string
    {
        $path = $media?->url ?: $media?->path ?: $track?->image_path;

        if (blank($path)) {
            return '/images/skill_up.png';
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::disk($media?->disk ?: 'public')->url($path);
    }

    public function trackCategory(?string $phase): string
    {
        return $phase === 'launch' ? 'Launch track' : Str::headline((string) $phase);
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    public function trackDuration(Collection $products): string
    {
        $durations = $products
            ->map(fn (Product $product) => $this->durationFromCohort($product->cohort))
            ->filter()
            ->unique()
            ->values();

        return $durations->first() ?? 'TBA';
    }

    public function durationFromCohort(?Cohort $cohort): string
    {
        if (! $cohort?->starts_at || ! $cohort?->ends_at) {
            return 'TBA';
        }

        $weeks = max(1, (int) ceil($cohort->starts_at->diffInDays($cohort->ends_at, true) / 7));

        if ($weeks >= 8) {
            return (int) round($weeks / 4).' months';
        }

        return $weeks.' weeks';
    }

    /**
     * @param  Collection<int, mixed>  $levels
     */
    public function levelLabel(Collection $levels): string
    {
        if ($levels->isEmpty()) {
            return 'Coming soon';
        }

        if ($levels->count() === 1) {
            return (string) $levels->first();
        }

        return $levels->first().' to '.$levels->last();
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    public function trackPrice(Collection $products): string
    {
        $prices = $products
            ->map(fn (Product $product) => $product->defaultPrice)
            ->filter()
            ->sortBy(fn (ProductPrice $price) => (float) $price->amount)
            ->values();

        return $prices->isNotEmpty() ? $this->money($prices->first()) : 'Waitlist';
    }

    public function seatsLabel(Product $product, ?Cohort $cohort): string
    {
        if ($product->unlimited_enrollment) {
            return 'Unlimited seats';
        }

        $cap = $product->enrollment_cap;

        if (! $cap) {
            return 'Limited seats';
        }

        $remaining = max(0, $cap - (int) ($cohort?->enrolled_count ?? 0));

        return $remaining.' of '.$cap.' seats left';
    }

    public function money(ProductPrice $price): string
    {
        return $this->moneyAmount($price->currency, $price->amount);
    }

    public function moneyAmount(string $currency, string|float|int|null $amount): string
    {
        if ($amount === null) {
            return $currency.' 0';
        }

        $value = (float) $amount;
        $decimals = floor($value) === $value ? 0 : 2;

        return $currency.' '.number_format($value, $decimals);
    }
}
