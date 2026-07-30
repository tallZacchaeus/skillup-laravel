<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Installment;
use App\Models\Catalog\Order;
use App\Models\Catalog\OrderItem;
use App\Models\Catalog\Payment;
use App\Models\Catalog\PaymentPlan;
use App\Models\Catalog\PaymentWebhookEvent;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPaymentPlan;
use App\Models\Catalog\ProductPrice;
use App\Models\Catalog\Receipt;
use App\Models\User;
use App\Notifications\InstallmentReminderNotification;
use App\Services\Payments\CheckoutOrderService;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        config([
            'services.paystack.secret_key' => 'test-secret',
            'services.paystack.webhook_secret' => 'test-secret',
            'services.paystack.payment_url' => 'https://api.paystack.co',
        ]);
    }

    public function test_paystack_initialization_creates_pending_payment_with_subunit_amount(): void
    {
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/test',
                    'access_code' => 'access-test',
                    'reference' => 'ignored-provider-reference',
                ],
            ]),
        ]);

        $order = $this->orderForProduct(total: 200000);

        $payment = app(PaymentService::class)->initializePaystack($order);

        $this->assertSame(PaymentStatus::Pending, $payment->status);
        $this->assertSame('https://checkout.paystack.com/test', $payment->authorization_url);
        $this->assertSame($payment->reference, $order->fresh()->provider_reference);

        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.paystack.co/transaction/initialize'
            && $request['email'] === 'buyer@example.com'
            && $request['amount'] === 20000000
            && $request['currency'] === 'NGN'
            && $request['reference'] === $payment->reference);
    }

    public function test_successful_verification_marks_order_paid_and_creates_receipt_and_enrollment(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $order = $this->orderForProduct(user: $user, total: 200000);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'provider' => 'paystack',
            'reference' => 'PAY-VERIFY',
            'amount' => 200000,
        ]);

        Http::fake([
            'https://api.paystack.co/transaction/verify/PAY-VERIFY' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 'trx_123',
                    'status' => 'success',
                    'reference' => 'PAY-VERIFY',
                    'amount' => 20000000,
                    'currency' => 'NGN',
                    'gateway_response' => 'Successful',
                    'channel' => 'card',
                    'paid_at' => now()->toISOString(),
                ],
            ]),
        ]);

        app(PaymentService::class)->verifyPaystackReference('PAY-VERIFY');

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertSame('0.00', $order->fresh()->balance_due);
        $this->assertSame(1, Receipt::count());
        $this->assertSame(1, Enrollment::count());
        $this->assertSame(EnrollmentStatus::Pending, Enrollment::first()->status);
    }

    public function test_guest_checkout_creates_account_and_enrollment_on_payment(): void
    {
        \Spatie\Permission\Models\Role::findOrCreate('Learner');

        $product = $this->productWithPrice(amount: 200000);
        $price = $product->defaultPrice;

        $order = Order::factory()->create([
            'user_id' => null,
            'subtotal' => 200000,
            'total' => 200000,
            'balance_due' => 200000,
            'metadata' => [
                'customer' => [
                    'name' => 'Guest Buyer',
                    'email' => 'guest@example.com',
                    'phone' => '08000000000',
                ],
            ],
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_price_id' => $price->id,
            'product_title' => $product->title,
            'unit_amount' => 200000,
            'total' => 200000,
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'user_id' => null,
            'provider' => 'paystack',
            'reference' => 'PAY-GUEST',
            'amount' => 200000,
        ]);

        Http::fake([
            'https://api.paystack.co/transaction/verify/PAY-GUEST' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 'trx_guest',
                    'status' => 'success',
                    'reference' => 'PAY-GUEST',
                    'amount' => 20000000,
                    'currency' => 'NGN',
                    'gateway_response' => 'Successful',
                    'channel' => 'card',
                    'paid_at' => now()->toISOString(),
                ],
            ]),
        ]);

        app(PaymentService::class)->verifyPaystackReference('PAY-GUEST');

        $user = User::where('email', 'guest@example.com')->first();
        $this->assertNotNull($user, 'A learner account should be created for the guest.');
        $this->assertTrue($user->hasRole('Learner'));
        $this->assertSame($user->id, $order->fresh()->user_id);
        $this->assertSame(1, Enrollment::count());
        $this->assertSame($user->id, Enrollment::first()->user_id);
    }

    public function test_duplicate_paystack_webhooks_do_not_duplicate_receipts(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $order = $this->orderForProduct(user: $user, total: 200000);
        Payment::factory()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'provider' => 'paystack',
            'reference' => 'PAY-WEBHOOK',
            'amount' => 200000,
        ]);

        Http::fake([
            'https://api.paystack.co/transaction/verify/PAY-WEBHOOK' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 'trx_456',
                    'status' => 'success',
                    'reference' => 'PAY-WEBHOOK',
                    'amount' => 20000000,
                    'currency' => 'NGN',
                    'gateway_response' => 'Successful',
                    'channel' => 'card',
                    'paid_at' => now()->toISOString(),
                ],
            ]),
        ]);

        $payload = [
            'event' => 'charge.success',
            'data' => [
                'id' => 'trx_456',
                'reference' => 'PAY-WEBHOOK',
            ],
        ];
        $rawPayload = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha512', $rawPayload, 'test-secret');

        $this->call('POST', route('webhooks.paystack'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
        ], $rawPayload)->assertOk();

        $this->call('POST', route('webhooks.paystack'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
        ], $rawPayload)->assertOk();

        $this->assertSame(1, PaymentWebhookEvent::count());
        $this->assertSame(1, Receipt::count());
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
    }

    public function test_checkout_order_service_creates_installment_schedule(): void
    {
        $product = $this->productWithPrice(amount: 200000);
        $productPlan = ProductPaymentPlan::factory()->create([
            'product_id' => $product->id,
            'deposit_amount' => 50000,
            'installment_amount' => 75000,
            'installments_count' => 3,
        ]);

        $order = app(CheckoutOrderService::class)->create($product, [
            'name' => 'Buyer One',
            'email' => 'buyer@example.com',
            'phone' => '08000000000',
            'discount_code' => null,
            'payment_mode' => 'installment',
            'product_payment_plan_id' => $productPlan->id,
        ]);

        $this->assertSame(1, PaymentPlan::count());
        $this->assertSame(3, Installment::count());
        $this->assertSame([50000.0, 75000.0, 75000.0], Installment::orderBy('installment_number')->pluck('amount')->map(fn ($amount) => (float) $amount)->all());
        $this->assertSame('installment', $order->metadata['checkout']['payment_mode']);
    }

    public function test_manual_payment_marks_order_paid_and_creates_receipt(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $order = $this->orderForProduct(user: $user, total: 200000);

        $payment = app(PaymentService::class)->recordManualPayment($order, 200000, $user, 'Bank transfer confirmed');

        $this->assertSame('manual', $payment->provider);
        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertSame(1, Receipt::count());
        $this->assertSame(1, Enrollment::count());
    }

    public function test_installment_reminder_command_sends_due_reminders_once(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $order = $this->orderForProduct(user: $user, total: 200000);
        $paymentPlan = PaymentPlan::factory()->create(['order_id' => $order->id]);
        $dueInstallment = Installment::factory()->create([
            'payment_plan_id' => $paymentPlan->id,
            'order_id' => $order->id,
            'installment_number' => 1,
            'amount' => 100000,
            'amount_paid' => 0,
            'due_at' => now()->addDay(),
        ]);
        $futureInstallment = Installment::factory()->create([
            'payment_plan_id' => $paymentPlan->id,
            'order_id' => $order->id,
            'installment_number' => 2,
            'amount' => 100000,
            'amount_paid' => 0,
            'due_at' => now()->addWeek(),
        ]);

        $this->artisan('skillup:installment-reminders')->assertExitCode(0);

        $this->assertNotNull($dueInstallment->fresh()->reminder_sent_at);
        $this->assertNull($futureInstallment->fresh()->reminder_sent_at);

        Notification::assertSentTo($user, InstallmentReminderNotification::class);
    }

    private function orderForProduct(?User $user = null, float $total = 200000): Order
    {
        $user ??= User::factory()->create(['email' => 'buyer@example.com']);
        $product = $this->productWithPrice(amount: $total);
        $price = $product->defaultPrice;
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'subtotal' => $total,
            'total' => $total,
            'balance_due' => $total,
            'metadata' => [
                'customer' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => '08000000000',
                ],
            ],
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_price_id' => $price->id,
            'product_title' => $product->title,
            'unit_amount' => $total,
            'total' => $total,
        ]);

        return $order;
    }

    private function productWithPrice(float $amount = 200000): Product
    {
        $product = Product::factory()->published()->create(['cohort_id' => null]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'amount' => $amount,
            'is_default' => true,
            'is_active' => true,
        ]);

        return $product->refresh();
    }
}
