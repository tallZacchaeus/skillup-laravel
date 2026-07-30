<?php

namespace Database\Factories\Catalog;

use App\Enums\EnrollmentStatus;
use App\Models\Catalog\Cohort;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'cohort_id' => Cohort::factory(),
            'order_id' => null,
            'order_item_id' => null,
            'corporate_account_id' => null,
            'status' => EnrollmentStatus::Pending,
            'access_starts_at' => null,
            'access_ends_at' => null,
            'moodle_user_id' => null,
            'moodle_course_id' => null,
            'moodle_enrollment_id' => null,
            'provisioned_at' => null,
            'failed_reason' => null,
            'metadata' => [],
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => EnrollmentStatus::Active,
            'access_starts_at' => now(),
            'provisioned_at' => now(),
        ]);
    }
}
