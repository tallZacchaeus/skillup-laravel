<?php

namespace Database\Factories\Programs;

use App\Models\Programs\ProgramEdition;
use App\Models\Programs\ProgramEditionTrack;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProgramEditionTrack>
 */
class ProgramEditionTrackFactory extends Factory
{
    protected $model = ProgramEditionTrack::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true).' Track';

        return [
            'program_edition_id' => ProgramEdition::factory(),
            'product_id' => null,
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'age_min' => 8,
            'age_max' => 13,
            'capacity' => 50,
            'summary' => fake()->sentence(10),
            'curriculum' => [
                ['week' => 'Week 1', 'focus' => 'Foundations'],
                ['week' => 'Week 2', 'focus' => 'Building blocks'],
            ],
            'sort_order' => 0,
            'metadata' => [],
        ];
    }
}
