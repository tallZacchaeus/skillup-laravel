<?php

namespace App\Http\Controllers;

use App\Models\Catalog\Product;
use App\Models\Content\Downloadable;
use App\Models\Content\Lead;
use App\Models\Content\ResourceCategory;
use App\Models\Operations\FormSubmission;
use App\Support\Catalog\CatalogPresenter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResourceController extends Controller
{
    private const PER_PAGE = 9;

    public function __construct(private readonly CatalogPresenter $presenter) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $categorySlug = (string) $request->query('category', '');
        $page = max(1, (int) $request->query('page', 1));

        $activeCategory = $categorySlug !== ''
            ? ResourceCategory::where('slug', $categorySlug)->first()
            : null;

        $query = Downloadable::query()
            ->with('category')
            ->where('status', 'published')
            ->when($activeCategory, fn (Builder $q) => $q->where('resource_category_id', $activeCategory->id))
            ->when($search !== '', function (Builder $q) use ($search) {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
                $q->where(fn (Builder $inner) => $inner
                    ->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term));
            })
            ->orderByDesc('updated_at');

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate(self::PER_PAGE, ['*'], 'page', $page)->withQueryString();

        $featured = null;
        if ($search === '' && ! $activeCategory && $page === 1) {
            $featured = Downloadable::with('category')
                ->where('status', 'published')
                ->orderByDesc('updated_at')
                ->first();
        }

        $featuredId = $featured?->id;
        $resources = $paginator->getCollection()
            ->reject(fn (Downloadable $d) => $d->id === $featuredId)
            ->map(fn (Downloadable $d) => $this->formatResource($d))
            ->values();

        return Inertia::render('Public/Resources/Index', [
            'resources' => $resources,
            'featuredResource' => $featured ? $this->formatResource($featured) : null,
            'categories' => $this->categories(),
            'filters' => ['search' => $search, 'category' => $categorySlug],
            'pagination' => [
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => self::PER_PAGE,
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'featuredCourses' => $this->featuredCourses(),
            'seo' => $this->seo($request, $activeCategory, $search),
        ]);
    }

    public function show(string $slug): Response
    {
        $downloadable = Downloadable::with('category')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $related = Downloadable::with('category')
            ->where('resource_category_id', $downloadable->resource_category_id)
            ->where('id', '!=', $downloadable->id)
            ->where('status', 'published')
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (Downloadable $d) => $this->formatResource($d))
            ->values();

        return Inertia::render('Public/Resources/Show', [
            'resource' => $this->formatResource($downloadable),
            'relatedResources' => $related,
        ]);
    }

    public function download(Request $request, string $slug): StreamedResponse
    {
        $downloadable = Downloadable::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        if (! Storage::disk('public')->exists($downloadable->file_path)) {
            abort(404, 'Resource file not found.');
        }

        // Gated resources capture a lead; ungated resources download directly.
        if ($downloadable->is_gated) {
            $validated = $request->validate([
                'email' => 'required|email|max:255',
                'name' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:255',
            ]);

            $lead = Lead::create([
                'email' => $validated['email'],
                'name' => $validated['name'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'type' => 'downloadable_resource',
                'metadata' => [
                    'downloadable_id' => $downloadable->id,
                    'downloadable_title' => $downloadable->title,
                ],
            ]);

            FormSubmission::create([
                'user_id' => $request->user()?->id,
                'lead_id' => $lead->id,
                'form_key' => 'downloadable_resource',
                'source_url' => $request->headers->get('referer'),
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'subject' => 'Resource download: '.$downloadable->title,
                'payload' => [
                    'downloadable_id' => $downloadable->id,
                    'downloadable_title' => $downloadable->title,
                ],
            ]);
        }

        $downloadable->increment('download_count');

        return Storage::disk('public')->download($downloadable->file_path);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatResource(Downloadable $downloadable): array
    {
        $filePath = $downloadable->file_path;
        $fileExists = $filePath && Storage::disk('public')->exists($filePath);

        return [
            'id' => $downloadable->id,
            'title' => $downloadable->title,
            'slug' => $downloadable->slug,
            'description' => $downloadable->description,
            'image' => $downloadable->cover_image ?: '/images/consistent.jpg',
            'category' => $downloadable->category
                ? ['name' => $downloadable->category->name, 'slug' => $downloadable->category->slug]
                : null,
            'fileType' => $filePath ? strtoupper(pathinfo($filePath, PATHINFO_EXTENSION)) : null,
            'fileSize' => $fileExists ? $this->humanSize((int) Storage::disk('public')->size($filePath)) : null,
            'updatedLabel' => $downloadable->updated_at?->translatedFormat('M j, Y'),
            'isGated' => (bool) $downloadable->is_gated,
            'downloadUrl' => route('resources.download', $downloadable->slug),
            'url' => route('resources.show', $downloadable->slug),
        ];
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024).' KB';
        }

        return $bytes.' B';
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function categories()
    {
        return ResourceCategory::withCount(['downloadables' => fn (Builder $q) => $q->where('status', 'published')])
            ->orderBy('name')
            ->get()
            ->filter(fn (ResourceCategory $category) => $category->downloadables_count > 0)
            ->map(fn (ResourceCategory $category) => [
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => $category->downloadables_count,
            ])
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function featuredCourses()
    {
        return Product::published()
            ->where('is_featured', true)
            ->whereHas('track', fn (Builder $q) => $q->published())
            ->with($this->presenter->productRelations())
            ->orderBy('sort_order')
            ->limit(3)
            ->get()
            ->map(fn (Product $product) => [
                'title' => $product->title,
                'image' => $this->presenter->mediaUrl(
                    $product->relationLoaded('media') ? $product->media->firstWhere('is_primary', true) : null,
                    $product->track,
                ),
                'price' => $product->defaultPrice ? $this->presenter->money($product->defaultPrice) : null,
                'url' => $product->track ? route('courses.products.show', [$product->track->slug, $product->slug]) : route('courses.index'),
            ])
            ->values();
    }

    /**
     * @return array<string, string|null>
     */
    private function seo(Request $request, ?ResourceCategory $category, string $search): array
    {
        $title = 'Free Learning Resources';
        $description = 'Download free guides, templates, and checklists from SkillUp to accelerate your tech learning and career.';

        if ($category) {
            $title = $category->name.' resources';
            $description = 'Free '.$category->name.' downloads from SkillUp — practical resources for learners and tech professionals.';
        } elseif ($search !== '') {
            $title = 'Search: '.$search;
        }

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $request->url(),
            'ogImage' => url('/images/hero.jpg'),
        ];
    }
}
