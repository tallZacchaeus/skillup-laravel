<?php

namespace Database\Factories\Catalog;

use App\Enums\ProductStatus;
use App\Models\Catalog\Track;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Track>
 */
class TrackFactory extends Factory
{
    protected $model = Track::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'title' => Str::title($title),
            'slug' => Str::slug($title),
            'phase' => 'launch',
            'status' => ProductStatus::Published,
            'summary' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'image_path' => null,
            'outcomes' => ['Portfolio project', 'Career clarity'],
            'tools' => ['Moodle', 'Mentorship'],
            'is_featured' => false,
            'sort_order' => 0,
            'metadata' => [],
        ];
    }
}
