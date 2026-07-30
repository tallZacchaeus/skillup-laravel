<?php

namespace Database\Factories\Content;

use App\Models\Content\Post;
use App\Models\Content\PostCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = rtrim($this->faker->sentence(6), '.');

        return [
            'post_category_id' => PostCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'content' => $this->faker->paragraphs(5, true),
            'summary' => $this->faker->sentence(15),
            'featured_image' => null,
            'status' => 'published',
            'published_at' => now()->subDays($this->faker->numberBetween(1, 60)),
        ];
    }
}
