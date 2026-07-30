<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Enums\ProgramEditionStatus;
use App\Models\Catalog\Cart;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Payment;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPrice;
use App\Models\Programs\Program;
use App\Models\Programs\ProgramEdition;
use App\Models\Programs\ProgramEditionTrack;
use App\Models\User;
use App\Services\Payments\CheckoutOrderService;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function courseWithPrice(): Product
    {
        $product = Product::factory()->published()->create(['cohort_id' => null]);
        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'amount' => 150000,
            'is_default' => true,
            'is_active' => true,
        ]);

        return $product->refresh();
    }

    public function test_user_can_add_and_remove_a_course(): void
    {
        $user = User::factory()->create();
        $product = $this->courseWithPrice();

        $this->actingAs($user)->post(route('cart.add', $product->slug))->assertRedirect();
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id]);
        $this->assertTrue($user->cart()->exists());

        $this->actingAs($user)->delete(route('cart.remove', $product->slug))->assertRedirect();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cart_page_renders_items(): void
    {
        $user = User::factory()->create();
        $product = $this->courseWithPrice();

        $this->actingAs($user)->post(route('cart.add', $product->slug));

        $this->actingAs($user)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Public/Cart')
                ->where('count', 1)
                ->has('items', 1)
                ->where('items.0.slug', $product->slug));
    }

    public function test_empty_cart_exposes_total_and_recommended_keys(): void
    {
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Public/Cart')
                ->where('count', 0)
                ->has('total')
                ->has('recommended'));
    }

    public function test_removing_an_item_flashes_an_undo_action(): void
    {
        $user = User::factory()->create();
        $product = $this->courseWithPrice();

        $this->actingAs($user)->post(route('cart.add', $product->slug));

        $this->actingAs($user)
            ->delete(route('cart.remove', $product->slug))
            ->assertRedirect()
            ->assertSessionHas('undo');
    }

    public function test_guest_can_add_to_a_session_cart(): void
    {
        $product = $this->courseWithPrice();

        $this->post(route('cart.add', $product->slug))->assertRedirect();

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id]);
        $this->assertDatabaseHas('carts', ['user_id' => null]);
    }

    public function test_already_enrolled_course_cannot_be_added(): void
    {
        $user = User::factory()->create();
        $product = $this->courseWithPrice();
        Enrollment::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)
            ->post(route('cart.add', $product->slug))
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_program_product_cannot_be_added(): void
    {
        $product = $this->courseWithPrice();

        $program = Program::create(['slug' => 'summer', 'name' => 'Summer', 'is_active' => true]);
        $edition = ProgramEdition::create([
            'program_id' => $program->id,
            'year' => 2026,
            'slug' => '2026',
            'title' => 'Summer 2026',
            'status' => ProgramEditionStatus::RegistrationOpen,
        ]);
        ProgramEditionTrack::create([
            'program_edition_id' => $edition->id,
            'product_id' => $product->id,
            'name' => 'Track',
            'slug' => 'track',
        ]);

        $this->post(route('cart.add', $product->slug))->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cart_checkout_creates_a_multi_item_order_and_pays_once(): void
    {
        Queue::fake();
        config([
            'services.paystack.secret_key' => 'test-secret',
            'services.paystack.webhook_secret' => 'test-secret',
            'services.paystack.payment_url' => 'https://api.paystack.co',
        ]);

        $user = User::factory()->create();
        $a = $this->courseWithPrice();
        $b = $this->courseWithPrice();

        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $a->id, 'unit_amount' => 150000, 'currency' => 'NGN']);
        $cart->items()->create(['product_id' => $b->id, 'unit_amount' => 150000, 'currency' => 'NGN']);

        $this->actingAs($user);
        $order = app(CheckoutOrderService::class)->createFromCart($cart->fresh(), [
            'name' => 'Buyer', 'email' => $user->email, 'phone' => null,
        ]);

        $this->assertCount(2, $order->items);
        $this->assertSame(300000.0, (float) $order->total);
        $this->assertSame(300000.0, (float) $order->balance_due);
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]); // cart consumed

        $payment = Payment::factory()->create([
            'order_id' => $order->id, 'user_id' => $user->id, 'provider' => 'paystack',
            'reference' => 'PAY-CART', 'amount' => 300000,
        ]);

        Http::fake([
            'https://api.paystack.co/transaction/verify/PAY-CART' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 'trx_cart', 'status' => 'success', 'reference' => 'PAY-CART',
                    'amount' => 30000000, 'currency' => 'NGN', 'gateway_response' => 'Successful',
                    'channel' => 'card', 'paid_at' => now()->toISOString(),
                ],
            ]),
        ]);

        app(PaymentService::class)->verifyPaystackReference('PAY-CART');

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertSame(2, Enrollment::count());
    }

    public function test_sold_out_course_is_blocked_at_checkout(): void
    {
        $user = User::factory()->create();
        $product = $this->courseWithPrice();
        $product->update(['unlimited_enrollment' => false, 'enrollment_cap' => 1]);
        Enrollment::factory()->create(['product_id' => $product->id, 'status' => EnrollmentStatus::Active]);

        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $product->id, 'unit_amount' => 150000, 'currency' => 'NGN']);

        $this->actingAs($user)
            ->post(route('cart.checkout.store'), ['name' => 'Buyer', 'email' => 'buyer@example.com'])
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_guest_cart_merges_into_user(): void
    {
        $product = $this->courseWithPrice();
        $user = User::factory()->create();

        // A guest session cart with one course.
        $guest = \App\Models\Catalog\Cart::create(['session_id' => 'guest-session-xyz']);
        $guest->items()->create(['product_id' => $product->id, 'unit_amount' => 150000, 'currency' => 'NGN']);

        app(\App\Services\Cart\CartService::class)->mergeGuestCartIntoUser($user, 'guest-session-xyz');

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $user->fresh()->cart->id,
            'product_id' => $product->id,
        ]);
        // Guest cart is consumed.
        $this->assertDatabaseMissing('carts', ['id' => $guest->id]);
    }
}
