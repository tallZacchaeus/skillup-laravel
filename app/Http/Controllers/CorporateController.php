<?php

namespace App\Http\Controllers;

use App\Models\Catalog\Track;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class CorporateController extends Controller
{
    /**
     * Public corporate training page. Passes the real, currently-available launch
     * tracks so the "training tracks" section is data-driven (no manual duplication).
     */
    public function __invoke(): Response
    {
        return Inertia::render('Public/Corporate', [
            'tracks' => $this->launchTracks(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function launchTracks(): array
    {
        if (! Schema::hasTable('tracks')) {
            return [];
        }

        return Track::query()
            ->where('status', 'published')
            ->where('phase', 'launch')
            // Only real, sellable tracks (those backing a published product) —
            // keeps placeholder/seed tracks out of the corporate page.
            ->whereHas('products', fn ($query) => $query->where('status', 'published'))
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Track $track) => [
                'title' => $track->title,
                'slug' => $track->slug,
                'summary' => $track->summary,
                'skills' => array_slice($track->tools ?? [], 0, 4),
                'url' => route('courses.show', $track->slug),
            ])
            ->values()
            ->all();
    }
}
