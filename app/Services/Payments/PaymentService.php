<?php

namespace App\Services\Payments;

use App\Enums\DiscountRedemptionStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\InstallmentStatus;
use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentPlanStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Models\Catalog\DiscountRedemption;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Installment;
use App\Models\Catalog\Invoice;
use App\Models\Catalog\Order;
use App\Models\Catalog\Payment;
use App\Models\Catalog\PaymentPlan;
use App\Models\Catalog\PaymentRefund;
use App\Models\Catalog\ProductPaymentPlan;
use App\Models\Catalog\Receipt;
use App\Models\User;
use App\Services\Discounts\DiscountEligibilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use RuntimeException;

class PaymentService
{
    public function __construct(
        private readonly PaystackClient $paystack,
        private readonly DiscountEligibilityService $discounts,
    ) {}

    public function initializePaystack(Order $order): Payment
    {
        $order->loadMissing(['user', 'items.product', 'paymentPlan.installments']);
        $amount = $this->payableAmount($order);

        if ($amount <= 0) {
            throw new RuntimeException('Order has no payable balance.');
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'provider' => 'paystack',
            'status' => PaymentStatus::Pending,
            'currency' => $order->currency,
            'amount' => $amount,
            'initialized_at' => now(),
            'metadata' => [
                'order_uuid' => $order->uuid,
                'order_number' => $order->order_number,
            ],
        ]);

        $payload = [
            'email' => $this->customerEmail($order),
            'amount' => $this->toSubunit($amount),
            'currency' => $order->currency,
            'reference' => $payment->reference,
            'callback_url' => route('checkout.paystack.callback'),
            'metadata' => [
                'order_uuid' => $order->uuid,
                'order_number' => $order->order_number,
                'payment_uuid' => $payment->uuid,
            ],
        ];

        $response = $this->paystack->initialize($payload);

        if (! $response->successful() || $response->json('status') !== true) {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'failed_at' => now(),
                'provider_payload' => $response->json(),
                'gateway_response' => $response->json('message', 'Paystack initialization failed.'),
            ]);

            if ($payment->user) {
                $payment->user->notify(new \App\Notifications\PaymentFailedNotification($payment));
            }

            throw new RuntimeException($payment->gateway_response ?? 'Paystack initialization failed.');
        }

        $payment->update([
            'access_code' => $response->json('data.access_code'),
            'authorization_url' => $response->json('data.authorization_url'),
            'provider_payload' => $response->json(),
        ]);

        $order->update([
            'payment_provider' => 'paystack',
            'provider_reference' => $payment->reference,
        ]);

        return $payment;
    }

    public function verifyPaystackReference(string $reference): ?Payment
    {
        $response = $this->paystack->verify($reference);

        if (! $response->successful() || $response->json('status') !== true) {
            $payment = Payment::where('reference', $reference)->first();

            if ($payment) {
                $payment->update([
                    'status' => PaymentStatus::Failed,
                    'failed_at' => now(),
                    'provider_payload' => $response->json(),
                    'gateway_response' => $response->json('message', 'Paystack verification failed.'),
                ]);

                if ($payment->user) {
                    $payment->user->notify(new \App\Notifications\PaymentFailedNotification($payment));
                }
            }

            return $payment;
        }

        $data = $response->json('data', []);
        $payment = Payment::query()->where('reference', $reference)->first();

        if (! $payment) {
            $orderUuid = data_get($data, 'metadata.order_uuid');
            $order = $orderUuid ? Order::where('uuid', $orderUuid)->first() : null;

            if (! $order) {
                return null;
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'provider' => 'paystack',
                'reference' => $reference,
                'status' => PaymentStatus::Pending,
                'currency' => data_get($data, 'currency', $order->currency),
                'amount' => ((float) data_get($data, 'amount', 0)) / 100,
                'metadata' => ['created_from_verification' => true],
            ]);
        }

        if (data_get($data, 'status') === 'success') {
            return $this->applySuccessfulPayment($payment, $data);
        }

        $payment->update([
            'status' => PaymentStatus::Failed,
            'failed_at' => now(),
            'provider_payload' => $data,
            'gateway_response' => data_get($data, 'gateway_response'),
        ]);

        if ($payment->user) {
            $payment->user->notify(new \App\Notifications\PaymentFailedNotification($payment));
        }

        return $payment;
    }

    /**
     * @param  array<string, mixed>  $providerPayload
     */
    public function applySuccessfulPayment(Payment $payment, array $providerPayload = []): Payment
    {
        if ($payment->status === PaymentStatus::Paid) {
            return $payment;
        }

        return DB::transaction(function () use ($payment, $providerPayload) {
            $payment->refresh();
            $order = $payment->order()->lockForUpdate()->with(['items.product', 'paymentPlan.installments', 'invoices', 'discountRedemptions'])->firstOrFail();
            $paidAt = data_get($providerPayload, 'paid_at') ? Carbon::parse(data_get($providerPayload, 'paid_at')) : now();

            $payment->update([
                'status' => PaymentStatus::Paid,
                'provider_transaction_id' => data_get($providerPayload, 'id', $payment->provider_transaction_id),
                'channel' => data_get($providerPayload, 'channel', $payment->channel),
                'gateway_response' => data_get($providerPayload, 'gateway_response', $payment->gateway_response),
                'paid_at' => $paidAt,
                'verified_at' => now(),
                'provider_payload' => $providerPayload ?: $payment->provider_payload,
            ]);

            $this->applyPaymentToInstallments($order, (float) $payment->amount, $paidAt);

            $amountPaid = min((float) $order->total, (float) $order->amount_paid + (float) $payment->amount);
            $balanceDue = max(0, (float) $order->total - $amountPaid);
            $order->update([
                'amount_paid' => $amountPaid,
                'balance_due' => $balanceDue,
                'status' => $balanceDue <= 0 ? OrderStatus::Paid : OrderStatus::PartiallyPaid,
                'payment_status' => $balanceDue <= 0 ? PaymentStatus::Paid : PaymentStatus::PartiallyPaid,
                'paid_at' => $balanceDue <= 0 ? $paidAt : null,
            ]);

            Receipt::firstOrCreate(
                ['payment_id' => $payment->id],
                [
                    'order_id' => $order->id,
                    'currency' => $payment->currency,
                    'amount' => $payment->amount,
                    'issued_at' => now(),
                    'metadata' => ['provider' => $payment->provider],
                ],
            );

            $order->invoices()->where('status', '!=', InvoiceStatus::Void->value)->update([
                'status' => $balanceDue <= 0 ? InvoiceStatus::Paid->value : InvoiceStatus::Issued->value,
                'paid_at' => $balanceDue <= 0 ? $paidAt : null,
            ]);

            $order->discountRedemptions()
                ->where('status', DiscountRedemptionStatus::Locked->value)
                ->get()
                ->each(fn (DiscountRedemption $redemption) => $this->discounts->markRedeemed($redemption));

            if ($balanceDue <= 0) {
                $this->createPendingEnrollments($order);
                $order->paymentPlan?->update([
                    'status' => PaymentPlanStatus::Completed,
                    'completed_at' => now(),
                ]);
            }

            if ($payment->user) {
                $payment->user->notify(new \App\Notifications\OrderPaidNotification($order));
            }

            return $payment->fresh();
        });
    }

    public function recordManualPayment(Order $order, float $amount, ?User $recordedBy = null, ?string $note = null): Payment
    {
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'provider' => 'manual',
            'reference' => 'MAN-'.now()->format('Ymd').'-'.Str::upper(Str::random(10)),
            'status' => PaymentStatus::Pending,
            'currency' => $order->currency,
            'amount' => $amount,
            'metadata' => [
                'recorded_by_user_id' => $recordedBy?->id,
                'note' => $note,
            ],
        ]);

        return $this->applySuccessfulPayment($payment, [
            'gateway_response' => 'Manual payment recorded',
            'channel' => 'manual',
        ]);
    }

    public function createInvoice(Order $order): Invoice
    {
        return Invoice::firstOrCreate(
            ['order_id' => $order->id],
            [
                'user_id' => $order->user_id,
                'currency' => $order->currency,
                'subtotal' => $order->subtotal,
                'discount_total' => $order->discount_total,
                'tax_total' => $order->tax_total,
                'total' => $order->total,
                'issued_at' => now(),
                'due_at' => now()->addDays(7),
                'metadata' => ['order_number' => $order->order_number],
            ],
        );
    }

    public function createInstallmentSchedule(Order $order, ProductPaymentPlan $productPlan): PaymentPlan
    {
        return DB::transaction(function () use ($order, $productPlan) {
            $plan = PaymentPlan::create([
                'order_id' => $order->id,
                'product_payment_plan_id' => $productPlan->id,
                'name' => $productPlan->name,
                'currency' => $order->currency,
                'total_amount' => $order->total,
                'deposit_amount' => min((float) $productPlan->deposit_amount, (float) $order->total),
                'installment_amount' => $productPlan->installment_amount,
                'installments_count' => $productPlan->installments_count,
                'interval' => $productPlan->interval,
                'starts_at' => now(),
                'metadata' => ['source_product_payment_plan_id' => $productPlan->id],
            ]);

            $remaining = (float) $order->total;

            for ($number = 1; $number <= $productPlan->installments_count; $number++) {
                $amount = $number === 1
                    ? min((float) $plan->deposit_amount, $remaining)
                    : min((float) $productPlan->installment_amount, $remaining);

                if ($number === $productPlan->installments_count) {
                    $amount = $remaining;
                }

                Installment::create([
                    'payment_plan_id' => $plan->id,
                    'order_id' => $order->id,
                    'installment_number' => $number,
                    'currency' => $order->currency,
                    'amount' => max(0, $amount),
                    'due_at' => $this->installmentDueDate($number, $productPlan->interval),
                    'metadata' => [],
                ]);

                $remaining = max(0, $remaining - $amount);
            }

            return $plan;
        });
    }

    public function createRefundRequest(Order $order, float $amount, ?string $reason = null, ?User $requestedBy = null): PaymentRefund
    {
        return PaymentRefund::create([
            'payment_id' => $order->payments()->where('status', PaymentStatus::Paid->value)->latest()->value('id'),
            'order_id' => $order->id,
            'requested_by_user_id' => $requestedBy?->id,
            'provider' => $order->payment_provider ?: 'paystack',
            'currency' => $order->currency,
            'amount' => min($amount, (float) $order->amount_paid),
            'reason' => $reason,
        ]);
    }

    /**
     * React to a Paystack refund webhook: record the refund and, when it fully
     * reverses the paid amount, cancel the order (which suspends enrollments +
     * Moodle access and releases discounts). Idempotent via provider_refund_id.
     *
     * @param  array<string, mixed>  $data  The webhook `data` payload.
     */
    public function handleRefundEvent(string $event, array $data): void
    {
        $reference = data_get($data, 'transaction.reference') ?? data_get($data, 'reference');

        if (blank($reference)) {
            return;
        }

        $payment = Payment::query()
            ->where('provider', 'paystack')
            ->where('reference', $reference)
            ->latest()
            ->first();

        $order = $payment?->order;

        if (! $order) {
            return;
        }

        $amount = round(((float) data_get($data, 'amount', 0)) / 100, 2);
        $providerRefundId = (string) (data_get($data, 'id') ?? '');

        $status = match ($event) {
            'refund.processed' => RefundStatus::Processed,
            'refund.failed' => RefundStatus::Failed,
            'refund.pending' => RefundStatus::Pending,
            default => RefundStatus::Processing,
        };

        DB::transaction(function () use ($order, $payment, $amount, $providerRefundId, $data, $status, $event) {
            $refund = PaymentRefund::firstOrNew(
                $providerRefundId !== ''
                    ? ['provider' => 'paystack', 'provider_refund_id' => $providerRefundId]
                    : ['order_id' => $order->id, 'provider' => 'paystack', 'reference' => (string) data_get($data, 'transaction.reference')],
            );

            $refund->fill([
                'payment_id' => $payment?->id,
                'order_id' => $order->id,
                'provider' => 'paystack',
                'reference' => (string) (data_get($data, 'transaction.reference') ?? data_get($data, 'reference')),
                'provider_refund_id' => $providerRefundId ?: $refund->provider_refund_id,
                'currency' => $order->currency,
                'amount' => $amount > 0 ? $amount : (float) $order->amount_paid,
                'status' => $status,
                'provider_payload' => $data,
                'processed_at' => $event === 'refund.processed' ? now() : $refund->processed_at,
            ]);
            $refund->save();

            // Full refund → revoke access. Partial refunds are recorded only.
            if ($event === 'refund.processed'
                && (float) $order->amount_paid > 0
                && $refund->amount >= (float) $order->amount_paid
                && $order->status !== OrderStatus::Cancelled
            ) {
                $this->cancelOrder($order->fresh(), 'Refund processed by Paystack');
            }
        });
    }

    public function cancelOrder(Order $order, ?string $reason = null): void
    {
        DB::transaction(function () use ($order, $reason) {
            $order->update([
                'status' => OrderStatus::Cancelled,
                'payment_status' => $order->amount_paid > 0 ? $order->payment_status : PaymentStatus::Failed,
                'metadata' => array_merge($order->metadata ?? [], ['cancellation_reason' => $reason]),
            ]);

            $order->installments()->where('status', InstallmentStatus::Pending->value)->update([
                'status' => InstallmentStatus::Cancelled->value,
            ]);
            $order->paymentPlan?->update(['status' => PaymentPlanStatus::Cancelled]);
            $order->discountRedemptions()->where('status', DiscountRedemptionStatus::Locked->value)->update([
                'status' => DiscountRedemptionStatus::Released->value,
                'released_at' => now(),
            ]);
            
            $order->enrollments()->get()->each(function (Enrollment $enrollment) {
                $enrollment->update(['status' => EnrollmentStatus::Suspended]);
                \App\Jobs\Lms\SuspendMoodleEnrollmentJob::dispatch($enrollment);
            });
        });
    }

    public function payableAmount(Order $order): float
    {
        $order->loadMissing('paymentPlan.installments');
        $installment = $order->paymentPlan?->installments
            ->where('status', InstallmentStatus::Pending)
            ->sortBy('installment_number')
            ->first();

        if ($installment) {
            return max(0, (float) $installment->amount - (float) $installment->amount_paid);
        }

        return max(0, (float) $order->balance_due);
    }

    private function toSubunit(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function customerEmail(Order $order): string
    {
        return $order->user?->email
            ?? data_get($order->metadata, 'customer.email')
            ?? throw new RuntimeException('Order customer email is required for payment.');
    }

    private function installmentDueDate(int $number, string $interval): \Illuminate\Support\Carbon
    {
        if ($number === 1) {
            return now();
        }

        return match ($interval) {
            'weekly' => now()->addWeeks($number - 1),
            'custom' => now()->addMonths($number - 1),
            default => now()->addMonths($number - 1),
        };
    }

    private function applyPaymentToInstallments(Order $order, float $amount, mixed $paidAt): void
    {
        $remaining = $amount;

        foreach ($order->paymentPlan?->installments()->where('status', InstallmentStatus::Pending->value)->orderBy('installment_number')->get() ?? [] as $installment) {
            if ($remaining <= 0) {
                break;
            }

            $payable = max(0, (float) $installment->amount - (float) $installment->amount_paid);
            $applied = min($remaining, $payable);
            $newPaid = (float) $installment->amount_paid + $applied;

            $installment->update([
                'amount_paid' => $newPaid,
                'status' => $newPaid >= (float) $installment->amount ? InstallmentStatus::Paid : InstallmentStatus::Pending,
                'paid_at' => $newPaid >= (float) $installment->amount ? $paidAt : null,
            ]);

            $remaining -= $applied;
        }
    }

    private function createPendingEnrollments(Order $order): void
    {
        // Guest checkouts arrive with no user_id. Resolve or create the learner
        // account from the captured customer email so they actually get course
        // and Moodle access (otherwise a paid order would grant nothing).
        $user = $this->resolveOrderUser($order);

        if (! $user) {
            return;
        }

        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $enrollment = Enrollment::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'product_id' => $item->product_id,
                    'order_item_id' => $item->id,
                ],
                [
                    'cohort_id' => $item->product?->cohort_id,
                    'order_id' => $order->id,
                    'corporate_account_id' => $order->corporate_account_id,
                    'status' => EnrollmentStatus::Pending,
                    'metadata' => ['source' => 'paid_order'],
                ],
            );

            if ($enrollment->status !== EnrollmentStatus::Active) {
                \App\Jobs\Lms\EnrollUserInMoodleJob::dispatch($enrollment);
            }
        }
    }

    /**
     * Return the order's learner, creating a linked account from the captured
     * customer email for guest checkouts. New accounts receive the Learner role
     * and a set-password email; the order is linked so downstream provisioning
     * (enrollment, Moodle, receipts) has a real user.
     */
    private function resolveOrderUser(Order $order): ?User
    {
        if ($order->user_id) {
            return $order->user;
        }

        $email = data_get($order->metadata, 'customer.email');

        if (blank($email)) {
            return null;
        }

        $email = Str::lower(trim((string) $email));
        $user = User::where('email', $email)->first();
        $isNew = false;

        if (! $user) {
            $user = User::create([
                'name' => data_get($order->metadata, 'customer.name') ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Str::password(24),
            ]);

            // A completed paid checkout is strong proof of email ownership.
            $user->forceFill(['email_verified_at' => now()])->save();
            $isNew = true;
        }

        if (! $user->hasRole('Learner')) {
            try {
                $user->assignRole('Learner');
            } catch (\Throwable $e) {
                Log::warning('Could not assign Learner role to order user: '.$e->getMessage());
            }
        }

        $order->forceFill(['user_id' => $user->id])->save();

        if ($isNew) {
            try {
                Password::sendResetLink(['email' => $email]);
            } catch (\Throwable $e) {
                Log::warning('Guest set-password email failed for '.$email.': '.$e->getMessage());
            }
        }

        return $user;
    }
}
