<?php

namespace Database\Seeders;

use App\Models\Catalog\Product;
use App\Models\Catalog\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Promotes each course's track "tools" into a real, filterable skill taxonomy
 * and attaches them to the products. Idempotent.
 */
class TagSeeder extends Seeder
{
    public function run(): void
    {
        Product::with('track')->get()->each(function (Product $product) {
            $tools = $product->track?->tools ?? [];

            if (empty($tools)) {
                return;
            }

            $tagIds = collect($tools)
                ->map(fn (string $tool) => Tag::firstOrCreate(
                    ['slug' => Str::slug($tool)],
                    ['name' => $tool],
                )->id)
                ->all();

            $product->tags()->syncWithoutDetaching($tagIds);
        });
    }
}
