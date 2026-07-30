<?php

namespace Database\Factories\Catalog;

use App\Enums\DiscountType;
use App\Enums\ScholarshipAwardStatus;
use App\Models\Catalog\ScholarshipAward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScholarshipAward>
 */
class ScholarshipAwardFactory extends Factory
{
    protected $model = ScholarshipAward::class;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'status' => ScholarshipAwardStatus::Active,
            'discount_type' => DiscountType::FullScholarship,
            'discount_value' => 100,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'awarded_at' => now(),
            'metadata' => [],
        ];
    }
}
