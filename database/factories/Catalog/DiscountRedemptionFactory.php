<?php

namespace Database\Factories\Catalog;

use App\Enums\DiscountRedemptionStatus;
use App\Enums\DiscountType;
use App\Models\Catalog\DiscountCode;
use App\Models\Catalog\DiscountRedemption;
use App\Models\Catalog\DiscountRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscountRedemption>
 */
class DiscountRedemptionFactory extends Factory
{
    protected $model = DiscountRedemption::class;

    public function definition(): array
    {
        $subtotal = 200000;
        $discountAmount = 20000;

        return [
            'discount_rule_id' => DiscountRule::factory()->active(),
            'discount_code_id' => DiscountCode::factory(),
            'email' => fake()->unique()->safeEmail(),
            'status' => DiscountRedemptionStatus::Locked,
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 10,
            'currency' => 'NGN',
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total_after_discount' => $subtotal - $discountAmount,
            'code' => null,
            'snapshot' => [],
            'locked_at' => now(),
            'metadata' => [],
        ];
    }
}
