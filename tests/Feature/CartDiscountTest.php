<?php

namespace Tests\Feature;

use App\Enums\DiscountType;
use App\Models\Catalog\Cart;
use App\Models\Catalog\DiscountCode;
use App\Models\Catalog\DiscountRule;
use App\Models\Catalog\Order;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPrice;
use App\Models\User;
use App\Services\Discounts\CartDiscountService;
use App\Services\Payments\CheckoutOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CartDiscountTest extends TestCase
{
    use RefreshDatabase;

    private function course(float $amount): Product
    {
        $product = Product::factory()->published()->create(['cohort_id' => null]);
        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'amount' => $amount,
            'currency' => 'NGN',
            'is_default' => true,
            'is_active' => true,
        ]);

        return $product->refresh();
    }

    /**
     * @param  array<int, Product>  $products
     */
    private function orderFor(User $user, array $products): Order
    {
        $cart = Cart::create(['user_id' => $user->id]);

        foreach ($products as $product) {
            $cart->items()->create([
                'product_id' => $product->id,
                'unit_amount' => $product->defaultPrice->amount,
                'currency' => 'NGN',
            ]);
        }

        $this->actingAs($user);

        return app(CheckoutOrderService::class)->createFromCart($cart->fresh(), [
            'name' => 'Buyer', 'email' => $user->email, 'phone' => null,
        ]);
    }

    private function code(string $code, array $ruleOverrides): DiscountCode
    {
        $rule = DiscountRule::factory()->active()->create(array_merge([
            'type' => DiscountType::Percentage,
            'requires_code' => true,
            'product_id' => null,
            'track_id' => null,
            'course_level_id' => null,
            'cohort_id' => null,
            'minimum_order_amount' => null,
            'per_email_limit' => 0,
            'per_user_limit' => 0,
        ], $ruleOverrides));

        return DiscountCode::factory()->create(['discount_rule_id' => $rule->id, 'code' => $code]);
    }

    public function test_percentage_code_discounts_all_eligible_items(): void
    {
        $user = User::factory()->create();
        $order = $this->orderFor($user, [$this->course(100000), $this->course(200000)]);
        $this->code('SAVE10', ['value' => 10]);

        app(CartDiscountService::class)->applyCode($order, 'SAVE10', $user->email, $user);
        $order->refresh();

        $this->assertSame(30000.0, (float) $order->discount_total);
        $this->assertSame(270000.0, (float) $order->total);
        $this->assertSame(270000.0, (float) $order->balance_due);
        $this->assertDatabaseHas('discount_redemptions', [
            'order_id' => $order->id,
            'product_id' => null,
            'discount_amount' => 30000,
        ]);
        $this->assertDatabaseCount('discount_redemptions', 1);
    }

    public function test_product_scoped_code_discounts_only_that_course(): void
    {
        $user = User::factory()->create();
        $a = $this->course(100000);
        $b = $this->course(200000);
        $order = $this->orderFor($user, [$a, $b]);
        $this->code('HALFA', ['value' => 50, 'product_id' => $a->id]);

        app(CartDiscountService::class)->applyCode($order, 'HALFA', $user->email, $user);
        $order->refresh();

        $this->assertSame(50000.0, (float) $order->discount_total);
        $this->assertSame(250000.0, (float) $order->total);
        $this->assertSame(50000.0, (float) $order->items->firstWhere('product_id', $a->id)->discount_amount);
        $this->assertSame(0.0, (float) $order->items->firstWhere('product_id', $b->id)->discount_amount);
    }

    public function test_invalid_code_is_rejected(): void
    {
        $user = User::factory()->create();
        $order = $this->orderFor($user, [$this->course(100000)]);

        $this->expectException(ValidationException::class);
        app(CartDiscountService::class)->applyCode($order, 'NOPE', $user->email, $user);
    }

    public function test_code_scoped_to_a_course_not_in_the_cart_is_rejected(): void
    {
        $user = User::factory()->create();
        $inCart = $this->course(100000);
        $other = $this->course(50000);
        $order = $this->orderFor($user, [$inCart]);
        $this->code('OTHER', ['value' => 10, 'product_id' => $other->id]);

        $this->expectException(ValidationException::class);
        app(CartDiscountService::class)->applyCode($order, 'OTHER', $user->email, $user);
    }
}
