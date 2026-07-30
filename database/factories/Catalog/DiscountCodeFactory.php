<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\DiscountCode;
use App\Models\Catalog\DiscountRule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DiscountCode>
 */
class DiscountCodeFactory extends Factory
{
    protected $model = DiscountCode::class;

    public function definition(): array
    {
        return [
            'discount_rule_id' => DiscountRule::factory()->active(),
            'code' => Str::upper(fake()->unique()->bothify('SKILLUP###')),
            'visibility' => 'private',
            'max_redemptions' => null,
            'redeemed_count' => 0,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
            'metadata' => [],
        ];
    }
}
