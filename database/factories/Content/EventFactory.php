<?php

namespace Database\Factories\Content;

use App\Models\Content\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $title = Str::title($this->faker->words(4, true));
        $start = now()->addDays($this->faker->numberBetween(3, 30))->setTime(16, 0);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'description' => $this->faker->paragraphs(2, true),
            'type' => $this->faker->randomElement(['webinar', 'workshop', 'info_session']),
            'starts_at' => $start,
            'ends_at' => (clone $start)->addHour(),
            'registration_limit' => null,
            'registered_count' => 0,
            'status' => 'upcoming',
        ];
    }

    public function past(): static
    {
        $start = now()->subDays($this->faker->numberBetween(3, 30))->setTime(16, 0);

        return $this->state(fn () => [
            'status' => 'completed',
            'starts_at' => $start,
            'ends_at' => (clone $start)->addHour(),
        ]);
    }

    public function full(): static
    {
        return $this->state(fn () => ['registration_limit' => 2, 'registered_count' => 2]);
    }
}
