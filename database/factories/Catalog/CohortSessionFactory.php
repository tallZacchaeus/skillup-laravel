<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Cohort;
use App\Models\Catalog\CohortSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CohortSession>
 */
class CohortSessionFactory extends Factory
{
    protected $model = CohortSession::class;

    public function definition(): array
    {
        $startsAt = now()->addDays(fake()->numberBetween(3, 30));

        return [
            'cohort_id' => Cohort::factory(),
            'title' => fake()->sentence(4),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHours(2),
            'delivery_mode' => 'online',
            'meeting_url' => 'https://meet.example.com/'.fake()->uuid(),
            'location' => null,
            'notes' => fake()->sentence(),
            'metadata' => [],
        ];
    }
}
