<?php

namespace App\Http\Controllers;

use App\Models\Catalog\Product;
use App\Models\Content\Faq;
use App\Models\Content\Partner;
use App\Models\Content\Post;
use App\Models\Content\Testimonial;
use App\Models\Programs\Program;
use App\Models\Programs\ProgramEdition;
use App\Support\Catalog\CatalogPresenter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(private readonly CatalogPresenter $presenter) {}

    public function __invoke(): Response
    {
        return Inertia::render('Public/Home', [
            'faqs' => Schema::hasTable('faqs')
                ? Faq::orderBy('sort_order')->get()
                : collect(),
            'testimonials' => Schema::hasTable('testimonials')
                ? Testimonial::where('is_featured', true)->get()
                : collect(),
            'partners' => Schema::hasTable('partners')
                ? Partner::where('is_active', true)->get()
                : collect(),
            'recentPosts' => Schema::hasTable('posts')
                ? Post::with('category')
                    ->where('status', 'published')
                    ->where('published_at', '<=', now())
                    ->orderByDesc('published_at')
                    ->limit(3)
                    ->get()
                : collect(),
            'programs' => $this->activePrograms(),
            'programCourses' => $this->programCourses(),
            'featuredCourses' => $this->featuredCourses(),
        ]);
    }

    /**
     * Individual courses from every active program's current edition (e.g. both
     * Summer AI tracks), formatted with program context so each card registers
     * through the /programs funnel rather than generic checkout.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function programCourses()
    {
        if (! Schema::hasTable('programs')) {
            return collect();
        }

        return Program::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->flatMap(function (Program $program) {
                $edition = $program->currentEdition();

                if (! $edition) {
                    return [];
                }

                $edition->loadMissing('tracks.product');

                $products = $edition->tracks
                    ->sortBy('sort_order')
                    ->map->product
                    ->filter()
                    ->values();

                if ($products->isEmpty()) {
                    return [];
                }

                $products->load($this->presenter->productRelations());

                $context = ['program' => ['slug' => $program->slug, 'name' => $program->name]];

                return $products
                    ->map(fn (Product $product) => $this->presenter->formatProduct($product, $context))
                    ->all();
            })
            ->values();
    }

    /**
     * Active programs (e.g. Summer AI) with their current edition, for the home page.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function activePrograms()
    {
        if (! Schema::hasTable('programs')) {
            return collect();
        }

        return Program::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Program $program) {
                $edition = $program->currentEdition();

                if (! $edition) {
                    return null;
                }

                $edition->loadMissing('tracks.product.defaultPrice');

                $prices = $edition->tracks
                    ->map(fn ($track) => $track->product?->defaultPrice)
                    ->filter();

                $seatsRemaining = $edition->tracks
                    ->map(fn ($track) => $track->seatsRemaining())
                    ->filter(fn ($seats) => $seats !== null);

                return [
                    'slug' => $program->slug,
                    'name' => $program->name,
                    'tagline' => $program->tagline,
                    'description' => $program->description,
                    'url' => route('programs.show', $program->slug),
                    'image' => $this->editionImage($edition),
                    'status' => $edition->status->value,
                    'statusLabel' => Str::headline($edition->status->value),
                    'acceptsRegistrations' => $edition->status->acceptsRegistrations(),
                    'startsOn' => $edition->starts_on?->toFormattedDateString(),
                    'endsOn' => $edition->ends_on?->toFormattedDateString(),
                    'venueName' => $edition->venue_name,
                    'trackCount' => $edition->tracks->count(),
                    'seatsRemaining' => $seatsRemaining->isNotEmpty() ? (int) $seatsRemaining->sum() : null,
                    'priceFrom' => $prices->isNotEmpty()
                        ? $this->presenter->moneyAmount(
                            $prices->first()->currency,
                            $prices->min(fn ($price) => (float) $price->amount),
                        )
                        : null,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Featured catalogue courses (excludes program-backed products, which register via the program funnel).
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function featuredCourses()
    {
        if (! Schema::hasTable('products')) {
            return collect();
        }

        return Product::published()
            ->where('is_featured', true)
            ->whereHas('track', fn (Builder $q) => $q->published())
            ->with($this->presenter->productRelations())
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(6)
            ->get()
            ->map(fn (Product $product) => $this->presenter->formatProduct($product))
            ->values();
    }

    private function editionImage(ProgramEdition $edition): string
    {
        $path = $edition->hero_image_path;

        if (blank($path)) {
            return '/images/skill_up.png';
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
