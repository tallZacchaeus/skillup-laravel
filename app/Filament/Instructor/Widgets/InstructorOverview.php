<?php

namespace App\Filament\Instructor\Widgets;

use App\Enums\CohortStatus;
use App\Enums\EnrollmentStatus;
use App\Filament\Instructor\Concerns\ScopesInstructorProfile;
use App\Models\Catalog\Cohort;
use App\Models\Catalog\CohortSession;
use App\Models\Catalog\Enrollment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Instructor KPI band. Every stat is backed by real data — cohorts, learners,
 * today's sessions, and sessions still missing notes. Metrics without a real
 * source (attendance, grading, escalations) are intentionally not shown.
 */
class InstructorOverview extends StatsOverviewWidget
{
    use ScopesInstructorProfile;

    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $cohortIds = Cohort::where('instructor_profile_id', static::instructorProfileId())->pluck('id');

        $activeCohorts = Cohort::whereIn('id', $cohortIds)
            ->whereIn('status', [CohortStatus::Open->value, CohortStatus::InProgress->value])
            ->count();

        $activeLearners = Enrollment::whereIn('cohort_id', $cohortIds)
            ->where('status', EnrollmentStatus::Active->value)
            ->count();

        $sessionsToday = CohortSession::whereIn('cohort_id', $cohortIds)
            ->whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()])
            ->count();

        // Past sessions with no notes yet — a real "do this next" signal.
        $notesPending = CohortSession::whereIn('cohort_id', $cohortIds)
            ->where('ends_at', '<', now())
            ->where(fn ($q) => $q->whereNull('notes')->orWhere('notes', ''))
            ->count();

        return [
            Stat::make('Active cohorts', number_format($activeCohorts))
                ->description(number_format($cohortIds->count()).' assigned in total')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Active learners', number_format($activeLearners))
                ->description('Across your cohorts')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color($activeLearners > 0 ? 'success' : 'gray'),

            Stat::make('Sessions today', number_format($sessionsToday))
                ->description($sessionsToday > 0 ? 'See today’s schedule below' : 'Nothing scheduled today')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($sessionsToday > 0 ? 'warning' : 'gray'),

            Stat::make('Notes pending', number_format($notesPending))
                ->description($notesPending > 0 ? 'Past sessions awaiting notes' : 'All caught up')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color($notesPending > 0 ? 'danger' : 'success'),
        ];
    }
}
