<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\DiscountEligibilityList;
use App\Models\Catalog\DiscountRule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DiscountEligibilityList>
 */
class DiscountEligibilityListFactory extends Factory
{
    protected $model = DiscountEligibilityList::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'discount_rule_id' => DiscountRule::factory()->active(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'source_filename' => null,
            'total_emails' => 0,
            'metadata' => [],
        ];
    }
}
