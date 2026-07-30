<?php

namespace Database\Factories\Catalog;

use App\Enums\DiscountType;
use App\Enums\ScholarshipApplicationStatus;
use App\Models\Catalog\ScholarshipApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScholarshipApplication>
 */
class ScholarshipApplicationFactory extends Factory
{
    protected $model = ScholarshipApplication::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'country' => 'Nigeria',
            'reason' => fake()->paragraph(),
            'status' => ScholarshipApplicationStatus::Submitted,
            'requested_discount_type' => DiscountType::FullScholarship,
            'requested_discount_value' => 100,
            'metadata' => [],
        ];
    }
}
