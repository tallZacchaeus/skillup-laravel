<?php

namespace Tests\Feature\Programs;

use App\Enums\ProgramEditionStatus;
use App\Models\Programs\Program;
use App\Models\Programs\ProgramEdition;
use App\Models\Programs\ProgramEditionTrack;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramIndexTest extends TestCase
{
    use RefreshDatabase;

    private function makeProgram(array $programAttrs, string $status): Program
    {
        $program = Program::factory()->create($programAttrs);
        $edition = ProgramEdition::factory()->create([
            'program_id' => $program->id,
            'status' => $status,
            'starts_on' => now()->addDays(20),
            'ends_on' => now()->addDays(48),
        ]);
        ProgramEditionTrack::factory()->create([
            'program_edition_id' => $edition->id,
            'age_min' => 8,
            'age_max' => 15,
        ]);

        return $program;
    }

    public function test_index_renders_with_derived_program_data(): void
    {
        $this->makeProgram(['slug' => 'summer-ai', 'is_active' => true], ProgramEditionStatus::RegistrationOpen->value);

        $this->get('/programs')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Programs/Index')
                ->where('featuredSlug', 'summer-ai')
                ->has('programs', 1, fn ($p) => $p
                    ->where('slug', 'summer-ai')
                    ->where('statusKey', 'open')
                    ->where('statusLabel', 'Open')
                    ->where('audience', 'Ages 8–15')
                    ->where('durationWeeks', 4)
                    ->etc())
                ->has('statusFilters')
            );
    }

    public function test_status_filters_only_include_real_facets(): void
    {
        $this->makeProgram(['slug' => 'open-prog', 'is_active' => true, 'sort_order' => 1], ProgramEditionStatus::RegistrationOpen->value);
        $this->makeProgram(['slug' => 'soon-prog', 'is_active' => true, 'sort_order' => 2], ProgramEditionStatus::Announced->value);

        $this->get('/programs')
            ->assertOk()
            ->assertInertia(function ($page) {
                $keys = collect($page->toArray()['props']['statusFilters'])->pluck('key')->all();
                // Only facets that a real programme matches appear; no "completed"/"closed".
                sort($keys);
                $this->assertSame(['coming-soon', 'open'], $keys);
            });
    }

    public function test_program_without_public_edition_is_excluded(): void
    {
        $program = Program::factory()->create(['slug' => 'draft-only', 'is_active' => true]);
        ProgramEdition::factory()->create(['program_id' => $program->id, 'status' => ProgramEditionStatus::Draft->value]);

        $this->get('/programs')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('programs', 0)->where('featuredSlug', null));
    }
}
