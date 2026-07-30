<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Order;
use App\Models\Catalog\OrderItem;
use App\Models\Catalog\Payment;
use App\Models\Catalog\PaymentRefund;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RefundWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        config(['services.paystack.webhook_secret' => 'test-secret']);
    }

    /**
     * @return array{order: Order, enrollment: Enrollment}
     */
    private function paidOrderWithEnrollment(float $amountPaid = 200000): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->published()->create(['cohort_id' => null]);
        $price = ProductPrice::factory()->create(['product_id' => $product->id, 'amount' => $amountPaid, 'is_default' => true, 'is_active' => true]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
            'currency' => 'NGN',
            'subtotal' => $amountPaid,
            'total' => $amountPaid,
            'amount_paid' => $amountPaid,
            'balance_due' => 0,
            'payment_provider' => 'paystack',
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_price_id' => $price->id,
            'product_title' => $product->title,
            'unit_amount' => $amountPaid,
            'total' => $amountPaid,
        ]);
        Payment::factory()->create([
            'order_id' => $order->id, 'user_id' => $user->id, 'provider' => 'paystack',
            'reference' => 'PAY-REF', 'status' => PaymentStatus::Paid, 'amount' => $amountPaid,
        ]);
        $enrollment = Enrollment::factory()->create([
            'user_id' => $user->id, 'product_id' => $product->id, 'order_id' => $order->id,
            'status' => EnrollmentStatus::Active,
        ]);

        return ['order' => $order, 'enrollment' => $enrollment];
    }

    private function postRefund(array $data, string $event = 'refund.processed'): \Illuminate\Testing\TestResponse
    {
        $payload = ['event' => $event, 'data' => $data];
        $raw = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha512', $raw, 'test-secret');

        return $this->call('POST', route('webhooks.paystack'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
        ], $raw);
    }

    public function test_full_refund_records_it_and_suspends_access(): void
    {
        ['order' => $order, 'enrollment' => $enrollment] = $this->paidOrderWithEnrollment(200000);

        $this->postRefund([
            'id' => 'rf_1', 'status' => 'processed', 'amount' => 20000000, 'currency' => 'NGN',
            'transaction' => ['reference' => 'PAY-REF'],
        ])->assertOk();

        $this->assertDatabaseHas('payment_refunds', [
            'order_id' => $order->id, 'provider_refund_id' => 'rf_1', 'status' => 'processed', 'amount' => 200000,
        ]);
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(EnrollmentStatus::Suspended, $enrollment->fresh()->status);
        Queue::assertPushed(\App\Jobs\Lms\SuspendMoodleEnrollmentJob::class);
    }

    public function test_partial_refund_is_recorded_but_keeps_access(): void
    {
        ['order' => $order, 'enrollment' => $enrollment] = $this->paidOrderWithEnrollment(200000);

        $this->postRefund([
            'id' => 'rf_partial', 'status' => 'processed', 'amount' => 5000000, 'currency' => 'NGN',
            'transaction' => ['reference' => 'PAY-REF'],
        ])->assertOk();

        $this->assertDatabaseHas('payment_refunds', ['order_id' => $order->id, 'amount' => 50000, 'status' => 'processed']);
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertSame(EnrollmentStatus::Active, $enrollment->fresh()->status);
    }

    public function test_refund_webhook_is_idempotent(): void
    {
        ['order' => $order] = $this->paidOrderWithEnrollment(200000);

        $data = ['id' => 'rf_dup', 'status' => 'processed', 'amount' => 20000000, 'currency' => 'NGN', 'transaction' => ['reference' => 'PAY-REF']];
        $this->postRefund($data)->assertOk();
        $this->postRefund($data)->assertOk();

        $this->assertSame(1, PaymentRefund::where('order_id', $order->id)->count());
    }
}
