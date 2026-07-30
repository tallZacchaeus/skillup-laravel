<?php

namespace Database\Factories\Content;

use App\Models\Content\Downloadable;
use App\Models\Content\ResourceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Downloadable>
 */
class DownloadableFactory extends Factory
{
    protected $model = Downloadable::class;

    public function definition(): array
    {
        $title = ucfirst($this->faker->words(4, true));

        return [
            'resource_category_id' => ResourceCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'description' => $this->faker->sentence(14),
            'file_path' => 'resources/'.Str::slug($title).'.pdf',
            'cover_image' => null,
            'download_count' => 0,
            'status' => 'published',
            'is_gated' => true,
        ];
    }

    public function ungated(): static
    {
        return $this->state(fn () => ['is_gated' => false]);
    }
}
