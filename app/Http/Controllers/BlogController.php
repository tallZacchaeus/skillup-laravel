<?php

namespace App\Http\Controllers;

use App\Models\Catalog\Product;
use App\Models\Content\Post;
use App\Models\Content\PostCategory;
use App\Support\Catalog\CatalogPresenter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    private const PER_PAGE = 9;

    public function __construct(private readonly CatalogPresenter $presenter) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $categorySlug = (string) $request->query('category', '');
        $page = max(1, (int) $request->query('page', 1));

        $activeCategory = $categorySlug !== ''
            ? PostCategory::where('slug', $categorySlug)->first()
            : null;

        $query = Post::query()
            ->with('category')
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->when($activeCategory, fn (Builder $q) => $q->where('post_category_id', $activeCategory->id))
            ->when($search !== '', function (Builder $q) use ($search) {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
                $q->where(fn (Builder $inner) => $inner
                    ->where('title', 'like', $term)
                    ->orWhere('summary', 'like', $term)
                    ->orWhere('content', 'like', $term));
            })
            ->orderByDesc('published_at');

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate(self::PER_PAGE, ['*'], 'page', $page)->withQueryString();

        // Featured post: newest published article, only on the unfiltered first page.
        $featured = null;
        if ($search === '' && ! $activeCategory && $page === 1) {
            $featured = Post::with('category')
                ->where('status', 'published')
                ->where('published_at', '<=', now())
                ->orderByDesc('published_at')
                ->first();
        }

        $featuredId = $featured?->id;
        $posts = $paginator->getCollection()
            ->reject(fn (Post $post) => $post->id === $featuredId)
            ->map(fn (Post $post) => $this->formatPost($post))
            ->values();

        return Inertia::render('Public/Blog/Index', [
            'posts' => $posts,
            'featuredPost' => $featured ? $this->formatPost($featured, full: true) : null,
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
        $post = Post::with('category')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        $related = Post::with('category')
            ->where('post_category_id', $post->post_category_id)
            ->where('id', '!=', $post->id)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->limit(3)
            ->get()
            ->map(fn (Post $p) => $this->formatPost($p))
            ->values();

        return Inertia::render('Public/Blog/Show', [
            'post' => $this->formatPost($post, full: true),
            'relatedPosts' => $related,
            'structuredData' => $this->articleSchema($post),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPost(Post $post, bool $full = false): array
    {
        $data = [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'summary' => $post->summary ?: Str::limit(strip_tags((string) $post->content), 160),
            'image' => $post->featured_image ?: '/images/consistent.jpg',
            'category' => $post->category ? ['name' => $post->category->name, 'slug' => $post->category->slug] : null,
            'publishedAt' => $post->published_at?->toIso8601String(),
            'dateLabel' => $post->published_at?->translatedFormat('M j, Y'),
            'readingMinutes' => $this->readingMinutes($post->content),
            'url' => route('blog.show', $post->slug),
        ];

        if ($full) {
            $data['content'] = $post->content;
        }

        return $data;
    }

    private function readingMinutes(?string $content): int
    {
        $words = str_word_count(strip_tags((string) $content));

        return max(1, (int) ceil($words / 200));
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function categories()
    {
        return PostCategory::withCount(['posts' => fn (Builder $q) => $q
            ->where('status', 'published')
            ->where('published_at', '<=', now())])
            ->orderBy('name')
            ->get()
            ->filter(fn (PostCategory $category) => $category->posts_count > 0)
            ->map(fn (PostCategory $category) => [
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => $category->posts_count,
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
                'trackTitle' => $product->track?->title,
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
    private function seo(Request $request, ?PostCategory $category, string $search): array
    {
        $title = 'Blog & Insights';
        $description = 'Practical tips, career stories, and tech insights from SkillUp — helping you learn, grow, and thrive in tech.';

        if ($category) {
            $title = $category->name.' articles';
            $description = 'Read SkillUp articles on '.$category->name.' — practical guidance for learners and tech professionals.';
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

    /**
     * @return array<int, array<string, mixed>>
     */
    private function articleSchema(Post $post): array
    {
        $url = route('blog.show', $post->slug);
        $image = $post->featured_image ?: '/images/consistent.jpg';
        $image = Str::startsWith($image, ['http://', 'https://']) ? $image : url($image);

        $article = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => Str::limit(strip_tags((string) ($post->summary ?: $post->content)), 200),
            'image' => $image,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'url' => $url,
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
            'author' => ['@type' => 'Organization', 'name' => config('app.name', 'SkillUp')],
            'publisher' => ['@type' => 'Organization', 'name' => config('app.name', 'SkillUp')],
        ];

        $breadcrumb = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Blog', 'item' => route('blog.index')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $post->title, 'item' => $url],
            ],
        ];

        return [$article, $breadcrumb];
    }
}
