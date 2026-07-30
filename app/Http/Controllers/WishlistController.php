<?php

namespace App\Http\Controllers;

use App\Models\Catalog\Product;
use App\Support\Catalog\CatalogPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function __construct(private readonly CatalogPresenter $presenter) {}

    public function index(Request $request): Response
    {
        $products = $request->user()
            ->wishlistProducts()
            ->with($this->presenter->productRelations())
            ->orderByDesc('wishlist_items.created_at')
            ->get();

        return Inertia::render('Public/Wishlist', [
            'products' => $products
                ->map(fn (Product $product) => $this->presenter->formatProduct($product))
                ->values(),
        ]);
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        $result = $request->user()->wishlistProducts()->toggle($product->id);

        $saved = ! empty($result['attached']);

        return back()
            ->with('status', $saved ? 'Saved to your wishlist.' : 'Removed from your wishlist.')
            ->with('undo', $saved
                ? ['label' => 'Undo', 'url' => route('wishlist.toggle', $product), 'method' => 'post']
                : null);
    }
}
