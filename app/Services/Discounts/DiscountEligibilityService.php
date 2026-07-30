<?php

namespace App\Services\Discounts;

use App\Enums\DiscountRedemptionStatus;
use App\Enums\DiscountType;
use App\Models\Catalog\DiscountCode;
use App\Models\Catalog\DiscountEligibleEmail;
use App\Models\Catalog\DiscountEligibilityList;
use App\Models\Catalog\DiscountRedemption;
use App\Models\Catalog\DiscountRule;
use App\Models\Catalog\Order;
use App\Models\Catalog\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DiscountEligibilityService
{
    public function validate(
        string $email,
        Product $product,
        float $subtotal,
        ?string $code = null,
        ?User $user = null,
        bool $usesInstallments = false,
    ): DiscountValidationResult {
        $normalizedEmail = DiscountEligibleEmail::normalizeEmail($email);
        $discountCode = null;
        $rule = null;

        if ($code !== null && trim($code) !== '') {
            $discountCode = DiscountCode::query()
                ->active()
                ->where('code', strtoupper(trim($code)))
                ->with('discountRule')
                ->first();

            $rule = $discountCode?->discountRule;
        } else {
            $rule = DiscountRule::query()
                ->active()
                ->where('requires_code', false)
                ->where('is_public', true)
                ->get()
                ->first(fn (DiscountRule $candidate) => $this->passesRuleContext($candidate, $product, $subtotal, $usesInstallments));
        }

        if (! $rule || ! $rule->exists) {
            return DiscountValidationResult::invalid($code ? 'Discount code is invalid or inactive.' : 'No active discount is available.');
        }

        if (! DiscountRule::query()->active()->whereKey($rule->id)->exists()) {
            return DiscountValidationResult::invalid('Discount is not active for the current date.');
        }

        if ($rule->requires_code && ! $discountCode) {
            return DiscountValidationResult::invalid('A discount code is required.');
        }

        if (! $this->passesRuleContext($rule, $product, $subtotal, $usesInstallments)) {
            return DiscountValidationResult::invalid('Discount is not eligible for this course or checkout context.');
        }

        if ($discountCode && $discountCode->max_redemptions !== null && $this->codeUsage($discountCode) >= $discountCode->max_redemptions) {
            return DiscountValidationResult::invalid('Discount code usage limit has been reached.');
        }

        if ($rule->usage_limit !== null && $this->ruleUsage($rule) >= $rule->usage_limit) {
            return DiscountValidationResult::invalid('Discount usage limit has been reached.');
        }

        if ($rule->per_email_limit > 0 && $this->emailUsage($rule, $normalizedEmail) >= $rule->per_email_limit) {
            return DiscountValidationResult::invalid('This email has already used the maximum allowed discount redemptions.');
        }

        if ($user && $rule->per_user_limit > 0 && $this->userUsage($rule, $user) >= $rule->per_user_limit) {
            return DiscountValidationResult::invalid('This account has already used the maximum allowed discount redemptions.');
        }

        $eligibilityList = $this->matchingEligibilityList($rule, $normalizedEmail);

        if ($rule->requires_email_eligibility && ! $eligibilityList) {
            return DiscountValidationResult::invalid('This email is not eligible for this discount.');
        }

        $discountAmount = $this->discountAmount($rule, $subtotal);
        $snapshot = $this->snapshot($rule, $discountCode, $eligibilityList, $product, $normalizedEmail, $subtotal, $discountAmount);

        return DiscountValidationResult::valid($rule, $discountCode, $eligibilityList, $subtotal, $discountAmount, $snapshot);
    }

    public function lockForCheckout(
        Order $order,
        Product $product,
        string $email,
        float $subtotal,
        ?string $code = null,
        ?User $user = null,
        bool $usesInstallments = false,
    ): DiscountRedemption {
        $result = $this->validate($email, $product, $subtotal, $code, $user, $usesInstallments);

        if (! $result->valid || ! $result->rule) {
            throw ValidationException::withMessages([
                'discount' => $result->reason ?? 'Discount is invalid.',
            ]);
        }

        return DB::transaction(function () use ($order, $product, $email, $user, $result) {
            $redemption = DiscountRedemption::create([
                'discount_rule_id' => $result->rule->id,
                'discount_code_id' => $result->code?->id,
                'discount_eligibility_list_id' => $result->eligibilityList?->id,
                'user_id' => $user?->id ?? $order->user_id,
                'order_id' => $order->id,
                'product_id' => $product->id,
                'email' => $email,
                'status' => DiscountRedemptionStatus::Locked,
                'discount_type' => $result->rule->type,
                'discount_value' => $result->rule->value,
                'currency' => $result->rule->currency,
                'subtotal' => $result->subtotal,
                'discount_amount' => $result->discountAmount,
                'total_after_discount' => $result->totalAfterDiscount,
                'code' => $result->code?->code,
                'snapshot' => $result->snapshot,
                'locked_at' => now(),
            ]);

            $metadata = $order->metadata ?? [];
            $metadata['discount'] = $result->snapshot + [
                'redemption_id' => $redemption->id,
                'redemption_uuid' => $redemption->uuid,
            ];

            $order->forceFill([
                'discount_total' => $result->discountAmount,
                'total' => $result->totalAfterDiscount,
                'balance_due' => max(0, $result->totalAfterDiscount - (float) $order->amount_paid),
                'metadata' => $metadata,
            ])->save();

            return $redemption;
        });
    }

    public function markRedeemed(DiscountRedemption $redemption): void
    {
        if ($redemption->status === DiscountRedemptionStatus::Redeemed) {
            return;
        }

        DB::transaction(function () use ($redemption) {
            $redemption->update([
                'status' => DiscountRedemptionStatus::Redeemed,
                'redeemed_at' => now(),
            ]);

            $redemption->discountCode?->increment('redeemed_count');
        });
    }

    public function release(DiscountRedemption $redemption): void
    {
        if ($redemption->status !== DiscountRedemptionStatus::Locked) {
            return;
        }

        $redemption->update([
            'status' => DiscountRedemptionStatus::Released,
            'released_at' => now(),
        ]);
    }

    private function passesRuleContext(DiscountRule $rule, Product $product, float $subtotal, bool $usesInstallments): bool
    {
        if ($rule->minimum_order_amount !== null && $subtotal < (float) $rule->minimum_order_amount) {
            return false;
        }

        if ($usesInstallments && ! $rule->installment_compatible) {
            return false;
        }

        if ($rule->product_id && $rule->product_id !== $product->id) {
            return false;
        }

        if ($rule->track_id && $rule->track_id !== $product->track_id) {
            return false;
        }

        if ($rule->course_level_id && $rule->course_level_id !== $product->course_level_id) {
            return false;
        }

        if ($rule->cohort_id && $rule->cohort_id !== $product->cohort_id) {
            return false;
        }

        return true;
    }

    private function matchingEligibilityList(DiscountRule $rule, string $normalizedEmail): ?DiscountEligibilityList
    {
        return DiscountEligibilityList::query()
            ->where('discount_rule_id', $rule->id)
            ->whereHas('emails', fn ($query) => $query
                ->where('normalized_email', $normalizedEmail)
                ->where('status', 'active'))
            ->first();
    }

    private function discountAmount(DiscountRule $rule, float $subtotal): float
    {
        $amount = match ($rule->type) {
            DiscountType::Percentage => $subtotal * min(100, max(0, (float) $rule->value)) / 100,
            DiscountType::FixedAmount => (float) $rule->value,
            DiscountType::FullScholarship => $subtotal,
        };

        return round(min($subtotal, max(0, $amount)), 2);
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(
        DiscountRule $rule,
        ?DiscountCode $code,
        ?DiscountEligibilityList $eligibilityList,
        Product $product,
        string $normalizedEmail,
        float $subtotal,
        float $discountAmount,
    ): array {
        return [
            'discount_rule_id' => $rule->id,
            'discount_rule_uuid' => $rule->uuid,
            'discount_rule_name' => $rule->name,
            'discount_code_id' => $code?->id,
            'code' => $code?->code,
            'eligibility_list_id' => $eligibilityList?->id,
            'email' => $normalizedEmail,
            'type' => $rule->type->value,
            'value' => (float) $rule->value,
            'currency' => $rule->currency,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total_after_discount' => max(0, $subtotal - $discountAmount),
            'product_id' => $product->id,
            'product_slug' => $product->slug,
            'locked_at' => now()->toISOString(),
        ];
    }

    private function ruleUsage(DiscountRule $rule): int
    {
        return $rule->redemptions()
            ->whereIn('status', [DiscountRedemptionStatus::Locked->value, DiscountRedemptionStatus::Redeemed->value])
            ->count();
    }

    private function codeUsage(DiscountCode $code): int
    {
        return DiscountRedemption::query()
            ->where('discount_code_id', $code->id)
            ->whereIn('status', [DiscountRedemptionStatus::Locked->value, DiscountRedemptionStatus::Redeemed->value])
            ->count();
    }

    private function emailUsage(DiscountRule $rule, string $normalizedEmail): int
    {
        return $rule->redemptions()
            ->where('normalized_email', $normalizedEmail)
            ->whereIn('status', [DiscountRedemptionStatus::Locked->value, DiscountRedemptionStatus::Redeemed->value])
            ->count();
    }

    private function userUsage(DiscountRule $rule, User $user): int
    {
        return $rule->redemptions()
            ->where('user_id', $user->id)
            ->whereIn('status', [DiscountRedemptionStatus::Locked->value, DiscountRedemptionStatus::Redeemed->value])
            ->count();
    }
}
