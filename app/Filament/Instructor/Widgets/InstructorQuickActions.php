<?php

namespace App\Filament\Instructor\Widgets;

use App\Filament\Instructor\Resources\AssignedCohortResource;
use App\Filament\Instructor\Resources\InstructorLearnerResource;
use App\Filament\Instructor\Resources\InstructorSessionResource;
use Filament\Widgets\Widget;

/**
 * Quick-action shortcuts. Every card links to a real instructor resource — no
 * action is shown for a feature that has no backend yet (attendance capture,
 * announcements, escalations).
 */
class InstructorQuickActions extends Widget
{
    protected static string $view = 'filament.instructor.widgets.quick-actions';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    protected function getViewData(): array
    {
        return [
            'actions' => [
                [
                    'label' => 'Sessions & notes',
                    'description' => 'Manage your schedule and write session notes.',
                    'icon' => 'heroicon-o-calendar-days',
                    'url' => InstructorSessionResource::getUrl(),
                ],
                [
                    'label' => 'Your learners',
                    'description' => 'See who is enrolled across your cohorts.',
                    'icon' => 'heroicon-o-user-group',
                    'url' => InstructorLearnerResource::getUrl(),
                ],
                [
                    'label' => 'Assigned cohorts',
                    'description' => 'Review the cohorts you are teaching.',
                    'icon' => 'heroicon-o-rectangle-stack',
                    'url' => AssignedCohortResource::getUrl(),
                ],
            ],
        ];
    }
}
