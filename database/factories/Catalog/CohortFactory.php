<?php

namespace Database\Factories\Catalog;

use App\Enums\CohortStatus;
use App\Models\Catalog\Cohort;
use App\Models\Catalog\CourseLevel;
use App\Models\Catalog\InstructorProfile;
use App\Models\Catalog\Track;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cohort>
 */
class CohortFactory extends Factory
{
    protected $model = Cohort::class;

    public function definition(): array
    {
        $title = 'Cohort '.fake()->unique()->numberBetween(100, 999);

        return [
            'track_id' => Track::factory(),
            'course_level_id' => CourseLevel::factory(),
            'instructor_profile_id' => InstructorProfile::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'status' => CohortStatus::Open,
            'delivery_mode' => 'online',
            'timezone' => 'Africa/Lagos',
            'starts_at' => now()->addWeeks(2),
            'ends_at' => now()->addMonths(3),
            'enrollment_opens_at' => now()->subDay(),
            'enrollment_closes_at' => now()->addWeek(),
            'max_learners' => 40,
            'enrolled_count' => 0,
            'metadata' => [],
        ];
    }
}
