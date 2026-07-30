<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\LearnerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearnerProfile>
 */
class LearnerProfileFactory extends Factory
{
    protected $model = LearnerProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'phone' => fake()->phoneNumber(),
            'country' => 'Nigeria',
            'city' => fake()->city(),
            'headline' => fake()->sentence(4),
            'goals' => ['break_into_tech', 'build_portfolio'],
            'metadata' => [],
        ];
    }
}
