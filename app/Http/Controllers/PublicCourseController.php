<?php

namespace App\Http\Controllers;

use App\Models\Catalog\Product;
use App\Models\Catalog\Track;
use App\Models\Programs\Program;
use App\Support\Catalog\CatalogPresenter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Meilisearch\Client as MeilisearchClient;
use Throwable;

class PublicCourseController extends Controller
{
    private const PER_PAGE = 9;

    /** Price buckets used for both filtering and facet counts (NGN). */
    private const PRICE_BUCKETS = [
        ['key' => 'free', 'label' => 'Free', 'min' => 0.0, 'max' => 0.0],
        ['key' => 'under-100000', 'label' => 'Under ₦100,000', 'min' => 0.01, 'max' => 99999.99],
        ['key' => '100000-200000', 'label' => '₦100,000 – ₦200,000', 'min' => 100000.0, 'max' => 200000.0],
        ['key' => 'over-200000', 'label' => 'Over ₦200,000', 'min' => 200000.01, 'max' => null],
    ];

    private const SORTS = [
        ['key' => 'featured', 'label' => 'Featured'],
        ['key' => 'newest', 'label' => 'Newest'],
        ['key' => 'price_asc', 'label' => 'Price: low to high'],
        ['key' => 'price_desc', 'label' => 'Price: high to low'],
        ['key' => 'title', 'label' => 'A – Z'],
    ];

    public function __construct(private readonly CatalogPresenter $presenter) {}

    public function index(Request $request): Response
    {
        [$programProducts, $programOptions] = $this->programProductMap();

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'program' => (string) $request->query('program', ''),
            'category' => (string) $request->query('category', ''),
            'level' => (string) $request->query('level', ''),
            'delivery' => (string) $request->query('delivery', ''),
            'tags' => (string) $request->query('tags', ''),
            'price' => (string) $request->query('price', ''),
            'sort' => (string) $request->query('sort', 'featured'),
        ];
        $page = max(1, (int) $request->query('page', 1));

        // Prefer Meilisearch (relevance, typo-tolerance, fast facets); fall back
        // to database filtering if the search service is unavailable.
        $payload = $this->searchViaMeilisearch($filters, $page, $programProducts, $programOptions)
            ?? $this->searchViaDatabase($filters, $page, $programProducts, $programOptions);

        return Inertia::render('Public/Courses/Index', $payload);
    }

    public function showTrack(Request $request, string $trackSlug): Response
    {
        $track = Track::published()
            ->where('slug', $trackSlug)
            ->with([
                'levels',
                'products' => fn ($query) => $query
                    ->published()
                    ->with($this->presenter->productRelations())
                    ->orderBy('sort_order')
                    ->orderBy('title'),
            ])
            ->firstOrFail();

        $formatted = $this->presenter->formatTrack($track);

        return Inertia::render('Public/Courses/Show', [
            'trackSlug' => $track->slug,
            'track' => $formatted,
            'related' => $this->relatedTracks($track->id),
            'seo' => [
                'title' => $formatted['title'],
                'description' => Str::limit(strip_tags((string) ($formatted['summary'] ?: $formatted['description'])), 160),
                'canonical' => $request->url(),
                'ogImage' => Str::startsWith((string) $formatted['image'], ['http://', 'https://'])
                    ? $formatted['image']
                    : url((string) $formatted['image']),
            ],
            'structuredData' => $this->trackStructuredData($track, $formatted, $request->url()),
        ]);
    }

    /**
     * Primary product of other published launch tracks — real "students also learn" cards.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function relatedTracks(int $excludeTrackId)
    {
        return Track::published()
            ->where('id', '!=', $excludeTrackId)
            ->whereHas('products', fn (Builder $q) => $q->published())
            ->with(['products' => fn ($q) => $q->published()->with($this->presenter->productRelations())->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->limit(3)
            ->get()
            ->map(fn (Track $track) => $this->presenter->formatProduct($track->products->first()))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $formatted
     * @return array<int, array<string, mixed>>
     */
    private function trackStructuredData(Track $track, array $formatted, string $url): array
    {
        $image = Str::startsWith((string) $formatted['image'], ['http://', 'https://'])
            ? $formatted['image']
            : url((string) $formatted['image']);

        $course = [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $formatted['title'],
            'description' => Str::limit(strip_tags((string) ($formatted['description'] ?: $formatted['summary'])), 300),
            'url' => $url,
            'image' => $image,
            'provider' => [
                '@type' => 'Organization',
                'name' => config('app.name', 'SkillUp'),
                'sameAs' => url('/'),
            ],
            'hasCourseInstance' => ['@type' => 'CourseInstance', 'courseMode' => 'Online'],
        ];

        $breadcrumb = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Courses', 'item' => route('courses.index')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $formatted['title'], 'item' => $url],
            ],
        ];

        return [$course, $breadcrumb];
    }

    public function showProduct(Request $request, string $trackSlug, string $productSlug): Response
    {
        $product = Product::published()
            ->where('slug', $productSlug)
            ->whereHas('track', fn ($query) => $query->published()->where('slug', $trackSlug))
            ->with([...$this->presenter->productRelations(), 'publishedReviews'])
            ->firstOrFail();

        $user = $request->user();
        $canReview = false;
        $myReview = null;

        if ($user) {
            $canReview = $user->enrollments()->where('product_id', $product->id)->exists();

            if ($canReview && $existing = $product->reviews()->where('user_id', $user->id)->first()) {
                $myReview = [
                    'rating' => $existing->rating,
                    'title' => $existing->title,
                    'body' => $existing->body,
                ];
            }
        }

        return Inertia::render('Public/Courses/Product', [
            'product' => $this->presenter->formatProduct($product),
            'structuredData' => $this->presenter->courseStructuredData($product),
            'canReview' => $canReview,
            'myReview' => $myReview,
        ]);
    }

    // ---------------------------------------------------------------------
    // Meilisearch path
    // ---------------------------------------------------------------------

    /**
     * @param  array<string, string>  $filters
     * @param  array<int, array{slug: string, name: string}>  $programProducts
     * @param  array<int, array{slug: string, name: string, count: int}>  $programOptions
     * @return array<string, mixed>|null  Null signals "fall back to the database".
     */
    private function searchViaMeilisearch(array $filters, int $page, array $programProducts, array $programOptions): ?array
    {
        if (config('scout.driver') !== 'meilisearch') {
            return null;
        }

        try {
            $client = new MeilisearchClient(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

            $result = $client->index((new Product)->searchableAs())->search($filters['search'], [
                'filter' => $this->meiliFilter($filters),
                'sort' => $this->meiliSort($filters['sort']),
                'facets' => ['track_slug', 'level', 'delivery_mode', 'tags', 'price_amount'],
                'limit' => self::PER_PAGE,
                'offset' => ($page - 1) * self::PER_PAGE,
            ]);

            $ids = collect($result->getHits())->pluck('id')->map(fn ($id) => (int) $id)->all();
            $products = Product::whereIn('id', $ids)
                ->with($this->presenter->productRelations())
                ->get()
                ->keyBy('id');

            $items = collect($ids)
                ->map(fn (int $id) => $products->get($id))
                ->filter()
                ->map(fn (Product $product) => $this->presenter->formatProduct(
                    $product,
                    isset($programProducts[$product->id]) ? ['program' => $programProducts[$product->id]] : [],
                ))
                ->values();

            $total = (int) $result->getEstimatedTotalHits();

            return [
                'products' => $items,
                'filters' => $filters,
                'options' => $this->meiliFilterOptions($result->getFacetDistribution() ?? [], $programOptions),
                'pagination' => $this->paginationMeta($total, $page),
                'engine' => 'meilisearch',
            ];
        } catch (Throwable $e) {
            Log::warning('Meilisearch course search failed, using database fallback: '.$e->getMessage());

            return null;
        }
    }

    /**
     * @param  array<string, string>  $filters
     * @return array<int, string>
     */
    private function meiliFilter(array $filters): array
    {
        $clauses = [];

        // Program scoping: a program filter shows that program's products only;
        // otherwise program-backed products are hidden from the general catalogue.
        if ($filters['program'] !== '') {
            $clauses[] = 'program_slug = "'.$this->escape($filters['program']).'"';
        } else {
            $clauses[] = 'is_program = false';
        }

        if ($filters['category'] !== '') {
            $clauses[] = 'track_slug = "'.$this->escape($filters['category']).'"';
        }

        if ($filters['level'] !== '') {
            $clauses[] = 'level = "'.$this->escape($filters['level']).'"';
        }

        if ($filters['delivery'] !== '') {
            $clauses[] = 'delivery_mode = "'.$this->escape($filters['delivery']).'"';
        }

        if ($filters['tags'] !== '') {
            $clauses[] = 'tags = "'.$this->escape($filters['tags']).'"';
        }

        $bucket = collect(self::PRICE_BUCKETS)->firstWhere('key', $filters['price']);
        if ($bucket) {
            $clauses[] = 'price_amount >= '.$bucket['min'];
            if ($bucket['max'] !== null) {
                $clauses[] = 'price_amount <= '.$bucket['max'];
            }
        }

        return $clauses;
    }

    /**
     * @return array<int, string>
     */
    private function meiliSort(string $sort): array
    {
        return match ($sort) {
            'newest' => ['published_at:desc'],
            'price_asc' => ['price_amount:asc'],
            'price_desc' => ['price_amount:desc'],
            'title' => ['title:asc'],
            default => ['is_featured:desc', 'sort_order:asc'],
        };
    }

    /**
     * @param  array<string, array<string, int>>  $distribution
     * @param  array<int, array{slug: string, name: string, count: int}>  $programOptions
     * @return array<string, mixed>
     */
    private function meiliFilterOptions(array $distribution, array $programOptions): array
    {
        $trackTitles = Track::published()->pluck('title', 'slug');

        $categories = collect($distribution['track_slug'] ?? [])
            ->map(fn (int $count, string $slug) => [
                'value' => $slug,
                'label' => $trackTitles[$slug] ?? Str::headline($slug),
                'count' => $count,
            ])
            ->sortBy('label')
            ->values();

        $levels = collect($distribution['level'] ?? [])
            ->map(fn (int $count, string $name) => ['value' => $name, 'label' => $name, 'count' => $count])
            ->sortBy('label')
            ->values();

        $deliveryModes = collect($distribution['delivery_mode'] ?? [])
            ->map(fn (int $count, string $mode) => ['value' => $mode, 'label' => Str::headline($mode), 'count' => $count])
            ->sortBy('label')
            ->values();

        $skills = collect($distribution['tags'] ?? [])
            ->map(fn (int $count, string $name) => ['value' => $name, 'label' => $name, 'count' => $count])
            ->sortByDesc('count')
            ->values();

        $priceDistribution = $distribution['price_amount'] ?? [];
        $priceBuckets = collect(self::PRICE_BUCKETS)
            ->map(function (array $bucket) use ($priceDistribution) {
                $count = 0;
                foreach ($priceDistribution as $amount => $n) {
                    $amount = (float) $amount;
                    if ($amount >= $bucket['min'] && ($bucket['max'] === null || $amount <= $bucket['max'])) {
                        $count += $n;
                    }
                }

                return ['value' => $bucket['key'], 'label' => $bucket['label'], 'count' => $count];
            })
            ->filter(fn (array $bucket) => $bucket['count'] > 0)
            ->values();

        return [
            'categories' => $categories,
            'levels' => $levels,
            'deliveryModes' => $deliveryModes,
            'skills' => $skills,
            'priceBuckets' => $priceBuckets,
            'programs' => $this->programFacet($programOptions),
            'sorts' => $this->sortOptions(),
        ];
    }

    // ---------------------------------------------------------------------
    // Database fallback path
    // ---------------------------------------------------------------------

    /**
     * @param  array<string, string>  $filters
     * @param  array<int, array{slug: string, name: string}>  $programProducts
     * @param  array<int, array{slug: string, name: string, count: int}>  $programOptions
     * @return array<string, mixed>
     */
    private function searchViaDatabase(array $filters, int $page, array $programProducts, array $programOptions): array
    {
        $query = Product::published()
            ->whereHas('track', fn (Builder $q) => $q->published())
            ->with($this->presenter->productRelations());

        if ($filters['program'] !== '') {
            $ids = collect($programProducts)
                ->filter(fn (array $meta) => $meta['slug'] === $filters['program'])
                ->keys()
                ->all();
            $query->whereIn('id', $ids ?: [0]);
        } elseif ($programProducts !== []) {
            $query->whereNotIn('id', array_keys($programProducts));
        }

        $this->applyDatabaseFilters($query, $filters);
        $this->applyDatabaseSort($query, $filters['sort']);

        $products = $query->paginate(self::PER_PAGE, ['*'], 'page', $page)->withQueryString();

        $items = $products->getCollection()
            ->map(fn (Product $product) => $this->presenter->formatProduct(
                $product,
                isset($programProducts[$product->id]) ? ['program' => $programProducts[$product->id]] : [],
            ))
            ->values();

        return [
            'products' => $items,
            'filters' => $filters,
            'options' => $this->databaseFilterOptions(array_keys($programProducts), $programOptions),
            'pagination' => $this->paginationMeta($products->total(), $page),
            'engine' => 'database',
        ];
    }

    /**
     * @param  Builder<Product>  $query
     * @param  array<string, string>  $filters
     */
    private function applyDatabaseFilters(Builder $query, array $filters): void
    {
        if ($filters['search'] !== '') {
            $term = '%'.str_replace(['%', '_'], ['\%', '\_'], $filters['search']).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('subtitle', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhereHas('track', fn (Builder $t) => $t->where('title', 'like', $term));
            });
        }

        if ($filters['category'] !== '') {
            $query->whereHas('track', fn (Builder $t) => $t->where('slug', $filters['category']));
        }

        if ($filters['level'] !== '') {
            $query->whereHas('level', fn (Builder $l) => $l->where('name', $filters['level']));
        }

        if ($filters['delivery'] !== '') {
            $query->where('delivery_mode', $filters['delivery']);
        }

        if ($filters['tags'] !== '') {
            $query->whereHas('tags', fn (Builder $t) => $t->where('name', $filters['tags']));
        }

        $bucket = collect(self::PRICE_BUCKETS)->firstWhere('key', $filters['price']);
        if ($bucket) {
            $query->whereHas('defaultPrice', function (Builder $p) use ($bucket) {
                $p->where('amount', '>=', $bucket['min']);
                if ($bucket['max'] !== null) {
                    $p->where('amount', '<=', $bucket['max']);
                }
            });
        }
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function applyDatabaseSort(Builder $query, string $sort): void
    {
        $priceSub = '(select amount from product_prices where product_prices.product_id = products.id and is_default = 1 limit 1)';

        match ($sort) {
            'newest' => $query->orderByDesc('published_at')->orderBy('title'),
            'price_asc' => $query->orderByRaw($priceSub.' asc')->orderBy('title'),
            'price_desc' => $query->orderByRaw($priceSub.' desc')->orderBy('title'),
            'title' => $query->orderBy('title'),
            default => $query->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('title'),
        };
    }

    /**
     * @param  array<int, int>  $programProductIds
     * @param  array<int, array{slug: string, name: string, count: int}>  $programOptions
     * @return array<string, mixed>
     */
    private function databaseFilterOptions(array $programProductIds, array $programOptions): array
    {
        /** @var Collection<int, Product> $base */
        $base = Product::published()
            ->whereHas('track', fn (Builder $q) => $q->published())
            ->when($programProductIds !== [], fn (Builder $q) => $q->whereNotIn('id', $programProductIds))
            ->with(['track', 'level', 'tags', 'defaultPrice' => fn ($q) => $q->active()])
            ->get();

        $categories = $base
            ->filter(fn (Product $p) => $p->track !== null)
            ->groupBy(fn (Product $p) => $p->track->slug)
            ->map(fn (Collection $group) => [
                'value' => $group->first()->track->slug,
                'label' => $group->first()->track->title,
                'count' => $group->count(),
            ])
            ->sortBy('label')
            ->values();

        $levels = $base
            ->filter(fn (Product $p) => $p->level !== null)
            ->groupBy(fn (Product $p) => $p->level->name)
            ->map(fn (Collection $group, string $name) => ['value' => $name, 'label' => $name, 'count' => $group->count()])
            ->sortBy('label')
            ->values();

        $deliveryModes = $base
            ->filter(fn (Product $p) => filled($p->delivery_mode))
            ->groupBy('delivery_mode')
            ->map(fn (Collection $group, string $mode) => ['value' => $mode, 'label' => Str::headline($mode), 'count' => $group->count()])
            ->sortBy('label')
            ->values();

        $skills = $base
            ->flatMap(fn (Product $p) => $p->relationLoaded('tags') ? $p->tags : collect())
            ->groupBy('name')
            ->map(fn (Collection $group, string $name) => ['value' => $name, 'label' => $name, 'count' => $group->count()])
            ->sortByDesc('count')
            ->values();

        $priceBuckets = collect(self::PRICE_BUCKETS)
            ->map(function (array $bucket) use ($base) {
                $count = $base->filter(function (Product $p) use ($bucket) {
                    $amount = $p->defaultPrice?->amount;
                    if ($amount === null) {
                        return false;
                    }
                    $amount = (float) $amount;

                    return $amount >= $bucket['min'] && ($bucket['max'] === null || $amount <= $bucket['max']);
                })->count();

                return ['value' => $bucket['key'], 'label' => $bucket['label'], 'count' => $count];
            })
            ->filter(fn (array $bucket) => $bucket['count'] > 0)
            ->values();

        return [
            'categories' => $categories,
            'levels' => $levels,
            'deliveryModes' => $deliveryModes,
            'skills' => $skills,
            'priceBuckets' => $priceBuckets,
            'programs' => $this->programFacet($programOptions),
            'sorts' => $this->sortOptions(),
        ];
    }

    // ---------------------------------------------------------------------
    // Shared helpers
    // ---------------------------------------------------------------------

    /**
     * Build the product_id => program-meta map plus program filter options,
     * from active programs' current editions.
     *
     * @return array{0: array<int, array{slug: string, name: string}>, 1: array<int, array{slug: string, name: string, count: int}>}
     */
    private function programProductMap(): array
    {
        $map = [];
        $options = [];

        foreach (Program::query()->where('is_active', true)->orderBy('sort_order')->get() as $program) {
            $edition = $program->currentEdition();

            if (! $edition) {
                continue;
            }

            $edition->loadMissing('tracks');
            $productIds = $edition->tracks->pluck('product_id')->filter()->unique()->values();

            if ($productIds->isEmpty()) {
                continue;
            }

            $options[] = ['slug' => $program->slug, 'name' => $program->name, 'count' => $productIds->count()];

            foreach ($productIds as $productId) {
                $map[(int) $productId] = ['slug' => $program->slug, 'name' => $program->name];
            }
        }

        return [$map, $options];
    }

    /**
     * @param  array<int, array{slug: string, name: string, count: int}>  $programOptions
     * @return Collection<int, array<string, mixed>>
     */
    private function programFacet(array $programOptions): Collection
    {
        return collect($programOptions)
            ->map(fn (array $p) => ['value' => $p['slug'], 'label' => $p['name'], 'count' => $p['count']])
            ->values();
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function sortOptions(): Collection
    {
        return collect(self::SORTS)->map(fn (array $s) => ['value' => $s['key'], 'label' => $s['label']])->values();
    }

    /**
     * @return array<string, int|null>
     */
    private function paginationMeta(int $total, int $page): array
    {
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $from = $total > 0 ? (($page - 1) * self::PER_PAGE) + 1 : null;
        $to = $total > 0 ? min($page * self::PER_PAGE, $total) : null;

        return [
            'currentPage' => $page,
            'lastPage' => $lastPage,
            'perPage' => self::PER_PAGE,
            'total' => $total,
            'from' => $from,
            'to' => $to,
        ];
    }

    private function escape(string $value): string
    {
        return str_replace('"', '\"', $value);
    }
}
