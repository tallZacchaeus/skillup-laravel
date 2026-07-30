<?php

namespace App\Http\Controllers;

use App\Models\Catalog\Product;
use App\Models\Catalog\Track;
use App\Models\Content\Post;
use App\Models\Programs\Program;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    /** Static public routes and their change frequency. */
    private const STATIC_ROUTES = [
        ['home', 'daily', '1.0'],
        ['courses.index', 'daily', '0.9'],
        ['programs.index', 'weekly', '0.8'],
        ['about', 'monthly', '0.5'],
        ['corporate', 'monthly', '0.6'],
        ['community', 'monthly', '0.5'],
        ['contact', 'monthly', '0.4'],
        ['blog.index', 'weekly', '0.6'],
        ['resources.index', 'weekly', '0.5'],
        ['events.index', 'weekly', '0.5'],
    ];

    public function sitemap(): Response
    {
        $urls = [];

        foreach (self::STATIC_ROUTES as [$name, $freq, $priority]) {
            if (\Illuminate\Support\Facades\Route::has($name)) {
                $urls[] = ['loc' => route($name), 'changefreq' => $freq, 'priority' => $priority];
            }
        }

        // Program-backed products are surfaced through /programs, not /courses.
        $programProductIds = Schema::hasTable('program_edition_tracks')
            ? \App\Models\Programs\ProgramEditionTrack::query()->pluck('product_id')->filter()->all()
            : [];

        if (Schema::hasTable('tracks')) {
            Track::published()
                ->where(fn ($q) => $q->whereNull('metadata')->orWhere('metadata', 'not like', '%"internal":true%'))
                ->get()
                ->each(function (Track $track) use (&$urls) {
                    $urls[] = ['loc' => route('courses.show', $track->slug), 'changefreq' => 'weekly', 'priority' => '0.7'];
                });
        }

        if (Schema::hasTable('products')) {
            Product::published()
                ->whereNotIn('id', $programProductIds)
                ->whereHas('track', fn ($q) => $q->published())
                ->with('track')
                ->get()
                ->each(function (Product $product) use (&$urls) {
                    if (! $product->track) {
                        return;
                    }
                    $urls[] = [
                        'loc' => route('courses.products.show', [$product->track->slug, $product->slug]),
                        'lastmod' => $product->updated_at?->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.8',
                    ];
                });
        }

        if (Schema::hasTable('programs')) {
            Program::where('is_active', true)->get()->each(function (Program $program) use (&$urls) {
                if ($program->currentEdition()) {
                    $urls[] = ['loc' => route('programs.show', $program->slug), 'changefreq' => 'weekly', 'priority' => '0.8'];
                }
            });
        }

        if (Schema::hasTable('posts')) {
            Post::where('status', 'published')
                ->where('published_at', '<=', now())
                ->get()
                ->each(function (Post $post) use (&$urls) {
                    $urls[] = [
                        'loc' => route('blog.show', $post->slug),
                        'lastmod' => $post->updated_at?->toAtomString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.6',
                    ];
                });
        }

        return response($this->render($urls), 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /learner',
            'Disallow: /instructor',
            'Disallow: /corporate-portal',
            'Disallow: /checkout',
            'Disallow: /program-registrations',
            'Disallow: /program-onboarding',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200)->header('Content-Type', 'text/plain');
    }

    /**
     * @param  array<int, array<string, string|null>>  $urls
     */
    private function render(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.htmlspecialchars($url['loc'], ENT_XML1).'</loc>'."\n";
            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>'.$url['lastmod'].'</lastmod>'."\n";
            }
            if (! empty($url['changefreq'])) {
                $xml .= '    <changefreq>'.$url['changefreq'].'</changefreq>'."\n";
            }
            if (! empty($url['priority'])) {
                $xml .= '    <priority>'.$url['priority'].'</priority>'."\n";
            }
            $xml .= '  </url>'."\n";
        }

        return $xml.'</urlset>'."\n";
    }
}
