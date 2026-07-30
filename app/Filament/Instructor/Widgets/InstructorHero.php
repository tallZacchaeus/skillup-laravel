<?php

namespace App\Filament\Instructor\Widgets;

use App\Filament\Instructor\Concerns\ScopesInstructorProfile;
use App\Filament\Instructor\Resources\InstructorSessionResource;
use App\Models\Catalog\Cohort;
use App\Models\Catalog\CohortSession;
use Filament\Widgets\Widget;

/**
 * Personalised, time-aware welcome banner for the instructor dashboard. Shows
 * the next real teaching session (with a join link) and today's priorities.
 * Everything is derived from real data; no fabricated status is shown.
 */
class InstructorHero extends Widget
{
    use ScopesInstructorProfile;

    protected static string $view = 'filament.instructor.widgets.hero';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -3;

    protected function getViewData(): array
    {
        $cohortIds = Cohort::where('instructor_profile_id', static::instructorProfileId())->pluck('id');

        $sessionsToday = CohortSession::whereIn('cohort_id', $cohortIds)
            ->whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()])
            ->count();

        $nextSession = CohortSession::whereIn('cohort_id', $cohortIds)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->with('cohort')
            ->first();

        $notesPending = CohortSession::whereIn('cohort_id', $cohortIds)
            ->where('ends_at', '<', now())
            ->where(fn ($q) => $q->whereNull('notes')->orWhere('notes', ''))
            ->count();

        return [
            'name' => auth()->user()?->name,
            'greeting' => $this->greeting(),
            'sessionsToday' => $sessionsToday,
            'notesPending' => $notesPending,
            'nextSession' => $nextSession,
            'sessionsUrl' => InstructorSessionResource::getUrl(),
        ];
    }

    private function greeting(): string
    {
        $hour = (int) now()->format('G');

        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 18 => 'Good afternoon',
            default => 'Good evening',
        };
    }
}
