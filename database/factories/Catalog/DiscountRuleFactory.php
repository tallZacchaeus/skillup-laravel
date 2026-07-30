<?php

namespace Database\Factories\Catalog;

use App\Enums\DiscountRuleStatus;
use App\Enums\DiscountType;
use App\Models\Catalog\DiscountRule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DiscountRule>
 */
class DiscountRuleFactory extends Factory
{
    protected $model = DiscountRule::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'status' => DiscountRuleStatus::Draft,
            'type' => DiscountType::Percentage,
            'value' => 15,
            'currency' => 'NGN',
            'minimum_order_amount' => null,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'usage_limit' => null,
            'per_email_limit' => 1,
            'per_user_limit' => 1,
            'requires_code' => true,
            'requires_email_eligibility' => false,
            'installment_compatible' => true,
            'stackable' => false,
            'is_public' => false,
            'metadata' => [],
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => DiscountRuleStatus::Active,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => DiscountRuleStatus::Active,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(),
        ]);
    }
}
