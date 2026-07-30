<?php

namespace Database\Factories\Content;

use App\Models\Content\ResourceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ResourceCategory>
 */
class ResourceCategoryFactory extends Factory
{
    protected $model = ResourceCategory::class;

    public function definition(): array
    {
        $name = ucwords($this->faker->unique()->words(2, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
