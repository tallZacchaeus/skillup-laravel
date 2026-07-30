<?php

namespace App\Services\Discounts;

use App\Enums\DiscountRedemptionStatus;
use App\Models\Catalog\DiscountCode;
use App\Models\Catalog\DiscountRedemption;
use App\Models\Catalog\DiscountRule;
use App\Models\Catalog\Order;
use App\Models\Catalog\OrderItem;
use App\Models\Catalog\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Applies a discount code to a multi-item cart order.
 *
 * Creates ONE order-level redemption (product_id null) so a single code counts
 * as a single use regardless of how many courses it discounts. All limit /
 * min-order / eligibility checks are delegated to DiscountEligibilityService
 * (single source of truth) using the eligible subtotal.
 */
class CartDiscountService
{
    public function __construct(private readonly DiscountEligibilityService $discounts) {}

    public function applyCode(Order $order, string $code, string $email, ?User $user = null): DiscountRedemption
    {
        $order->loadMissing('items.product');
        $rule = $this->resolveRuleByCode($code);

        $eligible = $order->items
            ->filter(fn (OrderItem $item) => $item->product && $this->ruleAppliesToProduct($rule, $item->product))
            ->values();

        if ($eligible->isEmpty()) {
            throw ValidationException::withMessages(['discount' => 'This code does not apply to the courses in your cart.']);
        }

        $eligibleSubtotal = (float) $eligible->sum(fn (OrderItem $item) => (float) $item->unit_amount);

        // Authoritative validation (usage/email/user limits, min-order, eligibility list) + amount.
        $result = $this->discounts->validate($email, $eligible->first()->product, $eligibleSubtotal, $code, $user, false);

        if (! $result->valid || ! $result->rule) {
            throw ValidationException::withMessages(['discount' => $result->reason ?? 'Discount is invalid.']);
        }

        if ($result->discountAmount <= 0) {
            throw ValidationException::withMessages(['discount' => 'This code has no effect on your cart.']);
        }

        return DB::transaction(function () use ($order, $result, $email, $user, $eligible, $eligibleSubtotal) {
            $discountAmount = $result->discountAmount;

            $redemption = DiscountRedemption::create([
                'discount_rule_id' => $result->rule->id,
                'discount_code_id' => $result->code?->id,
                'discount_eligibility_list_id' => $result->eligibilityList?->id,
                'user_id' => $user?->id ?? $order->user_id,
                'order_id' => $order->id,
                'product_id' => null, // order-level: one redemption per order
                'email' => $email,
                'status' => DiscountRedemptionStatus::Locked,
                'discount_type' => $result->rule->type,
                'discount_value' => $result->rule->value,
                'currency' => $result->rule->currency,
                'subtotal' => $eligibleSubtotal,
                'discount_amount' => $discountAmount,
                'total_after_discount' => max(0, $eligibleSubtotal - $discountAmount),
                'code' => $result->code?->code,
                'snapshot' => $result->snapshot,
                'locked_at' => now(),
            ]);

            $this->distribute($eligible, $discountAmount, $eligibleSubtotal);

            $order->refresh();
            $newTotal = max(0, (float) $order->subtotal - $discountAmount);
            $metadata = $order->metadata ?? [];
            $metadata['discount'] = $result->snapshot + [
                'redemption_id' => $redemption->id,
                'redemption_uuid' => $redemption->uuid,
            ];

            $order->forceFill([
                'discount_total' => $discountAmount,
                'total' => $newTotal,
                'balance_due' => max(0, $newTotal - (float) $order->amount_paid),
                'metadata' => $metadata,
            ])->save();

            return $redemption;
        });
    }

    private function resolveRuleByCode(string $code): DiscountRule
    {
        $discountCode = DiscountCode::query()
            ->active()
            ->where('code', strtoupper(trim($code)))
            ->with('discountRule')
            ->first();

        if (! $discountCode || ! $discountCode->discountRule) {
            throw ValidationException::withMessages(['discount' => 'Discount code is invalid or inactive.']);
        }

        return $discountCode->discountRule;
    }

    private function ruleAppliesToProduct(DiscountRule $rule, Product $product): bool
    {
        return (! $rule->product_id || $rule->product_id === $product->id)
            && (! $rule->track_id || $rule->track_id === $product->track_id)
            && (! $rule->course_level_id || $rule->course_level_id === $product->course_level_id)
            && (! $rule->cohort_id || $rule->cohort_id === $product->cohort_id);
    }

    /**
     * Split the order-level discount across eligible items proportionally,
     * assigning the rounding remainder to the last item.
     *
     * @param  Collection<int, OrderItem>  $eligible
     */
    private function distribute(Collection $eligible, float $discount, float $eligibleSubtotal): void
    {
        $remaining = $discount;
        $last = $eligible->count() - 1;

        foreach ($eligible->values() as $index => $item) {
            $share = $index === $last
                ? $remaining
                : round($discount * ((float) $item->unit_amount / $eligibleSubtotal), 2);
            $remaining = round($remaining - $share, 2);

            $item->update([
                'discount_amount' => $share,
                'total' => max(0, (float) $item->unit_amount - $share),
            ]);
        }
    }
}
