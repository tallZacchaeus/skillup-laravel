<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\InstructorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InstructorProfile>
 */
class InstructorProfileFactory extends Factory
{
    protected $model = InstructorProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->jobTitle(),
            'bio' => fake()->paragraph(),
            'skills' => fake()->randomElements(['Product', 'Design', 'Data', 'Software', 'Mentorship'], 3),
            'metadata' => [],
        ];
    }
}
