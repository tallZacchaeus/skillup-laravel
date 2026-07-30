<?php

namespace App\Services\Cart;

use App\Models\Catalog\Cart;
use App\Models\Catalog\Product;
use App\Models\Programs\ProgramEditionTrack;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * Resolve the current cart (by user, else guest session). Only creates a
     * row when $create is true — read paths (e.g. the shared nav prop) must not
     * create a cart for every anonymous visitor.
     */
    public function resolve(bool $create = false): ?Cart
    {
        $user = Auth::user();

        if ($user) {
            return $create
                ? Cart::firstOrCreate(['user_id' => $user->id])
                : Cart::where('user_id', $user->id)->first();
        }

        $sessionId = session()->getId();

        return $create
            ? Cart::firstOrCreate(['session_id' => $sessionId, 'user_id' => null])
            : Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
    }

    public function add(Product $product): Cart
    {
        $this->assertCatalogueCourse($product);

        $price = $product->defaultPrice()->where('is_active', true)->first();

        if (! $price) {
            throw ValidationException::withMessages(['cart' => 'This course is not currently available for purchase.']);
        }

        if ($this->isProgramProduct($product)) {
            throw ValidationException::withMessages(['cart' => 'Programs are registered through their own page, not the cart.']);
        }

        if (($user = Auth::user()) && $user->enrollments()->where('product_id', $product->id)->exists()) {
            throw ValidationException::withMessages(['cart' => 'You are already enrolled in this course.']);
        }

        $cart = $this->resolve(create: true);

        // Single-currency cart (v1).
        $existingCurrency = $cart->items()->whereNotNull('currency')->value('currency');
        if ($existingCurrency && $existingCurrency !== $price->currency) {
            throw ValidationException::withMessages(['cart' => 'Your cart already contains courses in a different currency.']);
        }

        $cart->items()->firstOrCreate(
            ['product_id' => $product->id],
            ['unit_amount' => $price->amount, 'currency' => $price->currency],
        );

        return $cart;
    }

    public function remove(Product $product): void
    {
        $this->resolve()?->items()->where('product_id', $product->id)->delete();
    }

    public function clear(): void
    {
        $this->resolve()?->items()->delete();
    }

    /**
     * @return Collection<int, \App\Models\Catalog\CartItem>
     */
    public function items(): Collection
    {
        $cart = $this->resolve();

        if (! $cart) {
            return new Collection;
        }

        return $cart->items()->with('product')->latest()->get();
    }

    /**
     * @return array{count: int, ids: array<int, int>}
     */
    public function summary(): array
    {
        $cart = $this->resolve();

        if (! $cart) {
            return ['count' => 0, 'ids' => []];
        }

        $ids = $cart->items()->pluck('product_id')->all();

        return ['count' => count($ids), 'ids' => $ids];
    }

    /** Fold a guest's session cart into the authenticated user's cart on login. */
    public function mergeGuestCartIntoUser(User $user, ?string $sessionId = null): void
    {
        $sessionId ??= session()->getId();

        $guest = Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->with('items')
            ->first();

        if (! $guest || $guest->items->isEmpty()) {
            $guest?->delete();

            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);
        $enrolledIds = $user->enrollments()->pluck('product_id')->all();

        foreach ($guest->items as $item) {
            if (in_array($item->product_id, $enrolledIds, true)) {
                continue;
            }

            $userCart->items()->firstOrCreate(
                ['product_id' => $item->product_id],
                ['unit_amount' => $item->unit_amount, 'currency' => $item->currency],
            );
        }

        $guest->delete();
    }

    private function assertCatalogueCourse(Product $product): void
    {
        $ok = Product::published()
            ->whereKey($product->id)
            ->whereHas('track', fn ($query) => $query->published())
            ->exists();

        if (! $ok) {
            throw ValidationException::withMessages(['cart' => 'This course is not available.']);
        }
    }

    private function isProgramProduct(Product $product): bool
    {
        return ProgramEditionTrack::where('product_id', $product->id)->exists();
    }
}
