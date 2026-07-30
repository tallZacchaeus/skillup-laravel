<?php

namespace Database\Factories\Programs;

use App\Enums\ProgramEditionStatus;
use App\Models\Programs\Program;
use App\Models\Programs\ProgramEdition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgramEdition>
 */
class ProgramEditionFactory extends Factory
{
    protected $model = ProgramEdition::class;

    public function definition(): array
    {
        $year = (int) now()->addYear()->format('Y');

        return [
            'program_id' => Program::factory(),
            'year' => $year,
            'slug' => (string) $year,
            'title' => fake()->words(3, true).' '.$year,
            'theme' => fake()->sentence(4),
            'status' => ProgramEditionStatus::RegistrationOpen,
            'starts_on' => now()->addMonths(2)->startOfWeek(),
            'ends_on' => now()->addMonths(3)->startOfWeek(),
            'schedule_text' => 'Mon–Fri, 9:30 AM – 2:30 PM',
            'delivery_mode' => 'in_person',
            'venue_name' => fake()->company().' Hub',
            'venue_address' => fake()->address(),
            'capacity_total' => 100,
            'payment_mode' => 'immediate',
            'age_reference_date' => null,
            'seat_hold_minutes' => 45,
            'allow_installments' => false,
            'content' => [],
            'registration_fields' => [
                ['key' => 'tshirt_size', 'label' => 'T-shirt size', 'type' => 'select', 'options' => ['XS', 'S', 'M', 'L'], 'required' => true],
            ],
            'contact_whatsapp' => '+2348000000000',
            'contact_email' => 'programs@example.test',
            'metadata' => [],
        ];
    }
}
