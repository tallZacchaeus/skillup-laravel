<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\AdminProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminProfile>
 */
class AdminProfileFactory extends Factory
{
    protected $model = AdminProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'job_title' => fake()->jobTitle(),
            'department' => fake()->randomElement(['Operations', 'Finance', 'Programs', 'Support']),
            'metadata' => [],
        ];
    }
}
