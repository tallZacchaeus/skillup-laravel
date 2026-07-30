<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\DiscountEligibleEmail;
use App\Models\Catalog\DiscountEligibilityList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscountEligibleEmail>
 */
class DiscountEligibleEmailFactory extends Factory
{
    protected $model = DiscountEligibleEmail::class;

    public function definition(): array
    {
        return [
            'discount_eligibility_list_id' => DiscountEligibilityList::factory(),
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'status' => 'active',
            'metadata' => [],
        ];
    }
}
