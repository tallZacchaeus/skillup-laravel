<?php

namespace Database\Factories\Programs;

use App\Enums\ProgramRegistrationStatus;
use App\Models\Programs\ProgramEdition;
use App\Models\Programs\ProgramEditionTrack;
use App\Models\Programs\ProgramRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProgramRegistration>
 */
class ProgramRegistrationFactory extends Factory
{
    protected $model = ProgramRegistration::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'program_edition_id' => ProgramEdition::factory(),
            'program_edition_track_id' => ProgramEditionTrack::factory(),
            'guardian_name' => fake()->name(),
            'guardian_email' => fake()->unique()->safeEmail(),
            'guardian_phone' => fake()->phoneNumber(),
            'guardian_whatsapp' => fake()->phoneNumber(),
            'participant_name' => fake()->firstName().' '.fake()->lastName(),
            'participant_dob' => now()->subYears(10)->subMonths(3)->toDateString(),
            'status' => ProgramRegistrationStatus::Started,
            'source' => 'web',
            'utm' => [],
            'custom_fields' => [],
            'metadata' => [],
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'status' => ProgramRegistrationStatus::EmailVerified,
            'email_verified_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => ProgramRegistrationStatus::Paid,
            'email_verified_at' => now(),
        ]);
    }
}
