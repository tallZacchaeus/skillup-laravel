<?php

namespace Database\Factories\Programs;

use App\Models\Programs\Program;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true).' Program';

        return [
            'slug' => Str::slug($name),
            'name' => Str::title($name),
            'tagline' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'is_active' => true,
            'sort_order' => 0,
            'metadata' => [],
        ];
    }
}
