<?php

namespace App\Http\Controllers;

use App\Models\Catalog\Order;
use App\Models\Catalog\Product;
use App\Services\Payments\CheckoutOrderService;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function details(Product $product): Response
    {
        $this->ensureProductIsCheckoutable($product);

        $product->load(['track', 'level', 'defaultPrice', 'paymentPlans' => fn ($query) => $query->where('is_active', true)]);

        return Inertia::render('Public/Checkout/Details', [
            'product' => $this->formatProduct($product),
        ]);
    }

    public function store(Request $request, Product $product, CheckoutOrderService $checkout): RedirectResponse
    {
        $this->ensureProductIsCheckoutable($product);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'discount_code' => ['nullable', 'string', 'max:255'],
            'payment_mode' => ['required', 'in:full,installment'],
            'product_payment_plan_id' => ['nullable', 'integer', 'exists:product_payment_plans,id'],
        ]);

        $order = $checkout->create($product, $data);

        return redirect()->route('checkout.orders.review', $order->uuid);
    }

    public function review(Order $order, PaymentService $payments): Response
    {
        $order->load(['items.product.track', 'paymentPlan.installments', 'invoices', 'discountRedemptions']);

        return Inertia::render('Public/Checkout/Review', [
            'order' => $this->formatOrder($order, $payments),
        ]);
    }

    public function pay(Order $order, PaymentService $payments): \Symfony\Component\HttpFoundation\Response
    {
        $payment = $payments->initializePaystack($order);

        return Inertia::location($payment->authorization_url);
    }

    public function paystackCallback(Request $request, PaymentService $payments): RedirectResponse
    {
        $reference = (string) $request->query('reference');
        $payment = $reference ? $payments->verifyPaystackReference($reference) : null;
        $order = $payment?->order;

        if (! $order) {
            return redirect()->route('checkout.status.failed')->with('error', 'Payment reference could not be verified.');
        }

        return redirect()->route($order->balance_due <= 0 ? 'checkout.orders.success' : 'checkout.orders.pending', $order->uuid);
    }

    public function processing(Order $order, PaymentService $payments): Response
    {
        return $this->status($order, $payments, 'processing');
    }

    public function success(Order $order, PaymentService $payments): Response
    {
        return $this->status($order, $payments, 'success');
    }

    public function failed(?Order $order = null, ?PaymentService $payments = null): Response
    {
        return Inertia::render('Public/Checkout/Status', [
            'state' => 'failed',
            'order' => $order && $payments ? $this->formatOrder($order, $payments) : null,
        ]);
    }

    public function pending(Order $order, PaymentService $payments): Response
    {
        return $this->status($order, $payments, 'pending');
    }

    private function status(Order $order, PaymentService $payments, string $state): Response
    {
        $order->load(['items.product.track', 'paymentPlan.installments', 'receipts', 'invoices']);

        return Inertia::render('Public/Checkout/Status', [
            'state' => $state,
            'order' => $this->formatOrder($order, $payments),
        ]);
    }

    private function ensureProductIsCheckoutable(Product $product): void
    {
        Product::published()
            ->whereKey($product->id)
            ->whereHas('track', fn ($query) => $query->published())
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'title' => $product->title,
            'track' => $product->track?->title,
            'trackSlug' => $product->track?->slug,
            'level' => $product->level?->name,
            'price' => $product->defaultPrice ? $this->money($product->defaultPrice->currency, $product->defaultPrice->amount) : null,
            'amount' => $product->defaultPrice?->amount,
            'currency' => $product->defaultPrice?->currency ?? 'NGN',
            'paymentPlans' => $product->paymentPlans->map(fn ($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'deposit' => $this->money($plan->currency, $plan->deposit_amount),
                'installment' => $this->money($plan->currency, $plan->installment_amount),
                'installmentsCount' => $plan->installments_count,
                'interval' => $plan->interval,
            ])->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatOrder(Order $order, PaymentService $payments): array
    {
        return [
            'uuid' => $order->uuid,
            'orderNumber' => $order->order_number,
            'status' => $order->status->value,
            'paymentStatus' => $order->payment_status->value,
            'currency' => $order->currency,
            'subtotal' => $this->money($order->currency, $order->subtotal),
            'discountTotal' => $this->money($order->currency, $order->discount_total),
            'total' => $this->money($order->currency, $order->total),
            'amountPaid' => $this->money($order->currency, $order->amount_paid),
            'balanceDue' => $this->money($order->currency, $order->balance_due),
            'balanceDueAmount' => (float) $order->balance_due,
            'payableAmount' => $this->money($order->currency, $payments->payableAmount($order)),
            'customer' => data_get($order->metadata, 'customer', []),
            'items' => $order->items->map(fn ($item) => [
                'title' => $item->product_title,
                'track' => $item->product?->track?->title,
                'total' => $this->money($order->currency, $item->total),
            ])->values(),
            'installments' => $order->paymentPlan?->installments->map(fn ($installment) => [
                'number' => $installment->installment_number,
                'status' => $installment->status->value,
                'amount' => $this->money($installment->currency, $installment->amount),
                'dueAt' => $installment->due_at?->toFormattedDateString(),
            ])->values() ?? [],
            'receipts' => $order->receipts->map(fn ($receipt) => [
                'number' => $receipt->receipt_number,
                'amount' => $this->money($receipt->currency, $receipt->amount),
                'issuedAt' => $receipt->issued_at?->toFormattedDateString(),
            ])->values(),
        ];
    }

    private function money(string $currency, string|float|int|null $amount): string
    {
        return $currency.' '.number_format((float) $amount, 0);
    }
}
