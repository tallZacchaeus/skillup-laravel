<?php

namespace App\Services\Discounts;

use App\Models\Catalog\DiscountCode;
use App\Models\Catalog\DiscountEligibilityList;
use App\Models\Catalog\DiscountRule;

class DiscountValidationResult
{
    /**
     * @param  array<string, mixed>  $snapshot
     */
    public function __construct(
        public readonly bool $valid,
        public readonly ?string $reason,
        public readonly ?DiscountRule $rule = null,
        public readonly ?DiscountCode $code = null,
        public readonly ?DiscountEligibilityList $eligibilityList = null,
        public readonly float $subtotal = 0,
        public readonly float $discountAmount = 0,
        public readonly float $totalAfterDiscount = 0,
        public readonly array $snapshot = [],
    ) {}

    public static function invalid(string $reason): self
    {
        return new self(valid: false, reason: $reason);
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    public static function valid(
        DiscountRule $rule,
        ?DiscountCode $code,
        ?DiscountEligibilityList $eligibilityList,
        float $subtotal,
        float $discountAmount,
        array $snapshot,
    ): self {
        return new self(
            valid: true,
            reason: null,
            rule: $rule,
            code: $code,
            eligibilityList: $eligibilityList,
            subtotal: $subtotal,
            discountAmount: $discountAmount,
            totalAfterDiscount: max(0, $subtotal - $discountAmount),
            snapshot: $snapshot,
        );
    }
}
