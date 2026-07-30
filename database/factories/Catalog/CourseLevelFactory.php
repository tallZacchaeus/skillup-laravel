<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\CourseLevel;
use App\Models\Catalog\Track;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CourseLevel>
 */
class CourseLevelFactory extends Factory
{
    protected $model = CourseLevel::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['Basic', 'Intermediate', 'Advanced']).' '.fake()->unique()->word();

        return [
            'track_id' => Track::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'rank' => fake()->numberBetween(1, 3),
            'status' => 'active',
            'summary' => fake()->sentence(),
            'metadata' => [],
        ];
    }
}
