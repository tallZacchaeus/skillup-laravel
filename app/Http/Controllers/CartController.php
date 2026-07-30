<?php

namespace App\Http\Controllers;

use App\Models\Catalog\CartItem;
use App\Models\Catalog\Product;
use App\Services\Cart\CartService;
use App\Services\Discounts\CartDiscountService;
use App\Services\Payments\CheckoutOrderService;
use App\Support\Catalog\CatalogPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CatalogPresenter $presenter,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Public/Cart', $this->cartPayload());
    }

    public function add(Product $product): RedirectResponse
    {
        $this->cart->add($product);

        return back()
            ->with('status', 'Added to your cart.')
            ->with('undo', ['label' => 'Undo', 'url' => route('cart.remove', $product), 'method' => 'delete']);
    }

    public function remove(Product $product): RedirectResponse
    {
        $this->cart->remove($product);

        return back()
            ->with('status', 'Removed from your cart.')
            ->with('undo', ['label' => 'Undo', 'url' => route('cart.add', $product), 'method' => 'post']);
    }

    public function checkout(Request $request): Response|RedirectResponse
    {
        $payload = $this->cartPayload();

        if ($payload['count'] === 0) {
            return redirect()->route('cart.index');
        }

        $user = $request->user();

        return Inertia::render('Public/Checkout/CartCheckout', [
            ...$payload,
            'customer' => [
                'name' => $user?->name ?? '',
                'email' => $user?->email ?? '',
                'phone' => '',
            ],
        ]);
    }

    public function placeOrder(Request $request, CheckoutOrderService $checkout, CartDiscountService $cartDiscounts): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'discount_code' => ['nullable', 'string', 'max:255'],
        ]);

        $cart = $this->cart->resolve();

        if (! $cart) {
            return redirect()->route('cart.index');
        }

        // Order creation + discount are atomic: an invalid code rolls the whole thing back.
        $order = DB::transaction(function () use ($checkout, $cartDiscounts, $cart, $data, $request) {
            $order = $checkout->createFromCart($cart, $data);

            if (filled($data['discount_code'] ?? null)) {
                $cartDiscounts->applyCode($order, $data['discount_code'], $data['email'], $request->user());
            }

            return $order;
        });

        return redirect()->route('checkout.orders.review', $order->uuid);
    }

    /**
     * @return array{items: \Illuminate\Support\Collection<int, array<string, mixed>>, currency: string, subtotal: string, count: int}
     */
    private function cartPayload(): array
    {
        $items = $this->cart->items();

        $products = Product::whereIn('id', $items->pluck('product_id')->all())
            ->with($this->presenter->productRelations())
            ->get()
            ->keyBy('id');

        $lines = $items
            ->filter(fn (CartItem $item) => $products->has($item->product_id))
            ->map(fn (CartItem $item) => [
                ...$this->presenter->formatProduct($products->get($item->product_id)),
                'unitAmount' => (float) $item->unit_amount,
            ])
            ->values();

        $currency = $items->first()?->currency ?? 'NGN';
        $subtotal = (float) $items->sum(fn (CartItem $item) => (float) $item->unit_amount);

        return [
            'items' => $lines,
            'currency' => $currency,
            'subtotal' => $currency.' '.number_format($subtotal, 0),
            // No cart-level discount/tax is applied until checkout, so the total
            // equals the subtotal here (never a fabricated tax/discount line).
            'total' => $currency.' '.number_format($subtotal, 0),
            'count' => $lines->count(),
            'recommended' => $this->recommendedProducts($items->pluck('product_id')->all()),
        ];
    }

    /**
     * Real featured courses not already in the cart — used for the empty-cart
     * recommendations and the in-cart cross-sell. Never fabricated.
     *
     * @param  array<int, int>  $excludeIds
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function recommendedProducts(array $excludeIds)
    {
        return Product::published()
            ->where('is_featured', true)
            ->whereNotIn('id', $excludeIds ?: [0])
            ->whereHas('track', fn ($query) => $query->published())
            ->with($this->presenter->productRelations())
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(4)
            ->get()
            ->map(fn (Product $product) => $this->presenter->formatProduct($product))
            ->values();
    }
}
