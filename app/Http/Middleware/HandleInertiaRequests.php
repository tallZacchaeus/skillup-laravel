<?php

namespace App\Http\Middleware;

use App\Models\Content\NavigationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'wishlist' => function () use ($request) {
                $user = $request->user();

                if (! $user || ! Schema::hasTable('wishlist_items')) {
                    return ['ids' => [], 'count' => 0];
                }

                $ids = $user->wishlistProducts()->pluck('products.id')->all();

                return ['ids' => $ids, 'count' => count($ids)];
            },
            'cart' => fn () => Schema::hasTable('carts')
                ? app(\App\Services\Cart\CartService::class)->summary()
                : ['count' => 0, 'ids' => []],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'error' => fn () => $request->session()->get('error'),
                'undo' => fn () => $request->session()->get('undo'),
            ],
            'navigation' => fn () => Schema::hasTable('navigation_items')
                ? NavigationItem::query()
                    ->where('is_active', true)
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->get(['label', 'url', 'location', 'target'])
                : [],
        ];
    }
}
