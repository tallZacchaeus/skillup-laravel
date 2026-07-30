<?php

namespace App\Filament\Instructor\Widgets;

use App\Filament\Instructor\Concerns\ScopesInstructorProfile;
use App\Filament\Instructor\Resources\AssignedCohortResource;
use App\Filament\Instructor\Resources\InstructorSessionResource;
use App\Models\Catalog\Cohort;
use App\Models\Catalog\CohortSession;
use Filament\Widgets\Widget;

/**
 * "Needs attention" list driven entirely by real signals: past sessions with no
 * notes, and active cohorts with no upcoming session scheduled. The whole
 * widget hides when there is nothing to act on — never a placeholder.
 */
class InstructorNeedsAttention extends Widget
{
    use ScopesInstructorProfile;

    protected static string $view = 'filament.instructor.widgets.needs-attention';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return static::taskCount() > 0;
    }

    protected static function cohortIds()
    {
        return Cohort::where('instructor_profile_id', static::instructorProfileId())->pluck('id');
    }

    protected static function notesPendingQuery()
    {
        return CohortSession::whereIn('cohort_id', static::cohortIds())
            ->where('ends_at', '<', now())
            ->where(fn ($q) => $q->whereNull('notes')->orWhere('notes', ''));
    }

    protected static function cohortsWithoutUpcomingQuery()
    {
        return Cohort::whereIn('id', static::cohortIds())
            ->whereIn('status', ['open', 'in_progress'])
            ->whereDoesntHave('sessions', fn ($q) => $q->where('starts_at', '>=', now()));
    }

    protected static function taskCount(): int
    {
        return static::notesPendingQuery()->count() + static::cohortsWithoutUpcomingQuery()->count();
    }

    protected function getViewData(): array
    {
        $notesPending = static::notesPendingQuery()
            ->with('cohort')
            ->orderByDesc('ends_at')
            ->limit(5)
            ->get();

        $cohortsWithoutUpcoming = static::cohortsWithoutUpcomingQuery()
            ->orderBy('title')
            ->limit(5)
            ->get();

        return [
            'notesPending' => $notesPending,
            'cohortsWithoutUpcoming' => $cohortsWithoutUpcoming,
            'sessionsUrl' => InstructorSessionResource::getUrl(),
            'cohortsUrl' => AssignedCohortResource::getUrl(),
        ];
    }
}
