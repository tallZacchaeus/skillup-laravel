<?php

namespace App\Filament\Instructor\Widgets;

use App\Filament\Instructor\Concerns\ScopesInstructorProfile;
use App\Models\Catalog\Cohort;
use App\Models\Catalog\CohortSession;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Str;

/**
 * Upcoming teaching schedule. A searchable, sortable table (with a sticky
 * header) of the instructor's real upcoming sessions — showing when each starts
 * relative to now, its delivery mode, a join link, and whether notes exist yet.
 */
class InstructorUpcomingSessions extends TableWidget
{
    use ScopesInstructorProfile;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Your upcoming schedule';

    public function table(Table $table): Table
    {
        $cohortIds = Cohort::where('instructor_profile_id', static::instructorProfileId())->pluck('id');

        return $table
            ->query(
                CohortSession::query()
                    ->whereIn('cohort_id', $cohortIds)
                    ->where('starts_at', '>=', now())
                    ->with('cohort'),
            )
            ->defaultSort('starts_at')
            ->defaultPaginationPageOption(10)
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Session')
                    ->weight('semibold')
                    ->searchable()
                    ->description(fn (CohortSession $r) => $r->cohort?->title),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('When')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state?->isToday()
                        ? 'Today · '.$state->format('g:i A')
                        : $state?->format('D, M j · g:i A'))
                    ->description(fn (CohortSession $r) => $r->starts_at?->diffForHumans())
                    ->badge(fn (CohortSession $r) => $r->starts_at?->isToday())
                    ->color(fn (CohortSession $r) => $r->starts_at?->isToday() ? 'warning' : null),

                Tables\Columns\TextColumn::make('delivery_mode')
                    ->label('Mode')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline((string) $state)),

                Tables\Columns\IconColumn::make('notes')
                    ->label('Notes')
                    ->alignCenter()
                    ->icon(fn ($state) => filled($state) ? 'heroicon-m-check-circle' : 'heroicon-m-minus-small')
                    ->color(fn ($state) => filled($state) ? 'success' : 'gray')
                    ->tooltip(fn ($state) => filled($state) ? 'Notes added' : 'No notes yet'),

                Tables\Columns\TextColumn::make('meeting_url')
                    ->label('Meeting')
                    ->formatStateUsing(fn ($state) => $state ? 'Join' : '—')
                    ->url(fn (CohortSession $record) => $record->meeting_url ?: null, shouldOpenInNewTab: true)
                    ->weight('semibold')
                    ->color('primary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cohort_id')
                    ->label('Cohort')
                    ->options(fn () => Cohort::whereIn('id', $cohortIds)->pluck('title', 'id')->all()),
                Tables\Filters\SelectFilter::make('delivery_mode')
                    ->label('Mode')
                    ->options(['online' => 'Online', 'in_person' => 'In person', 'hybrid' => 'Hybrid']),
            ])
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->emptyStateHeading('No upcoming sessions')
            ->emptyStateDescription('Sessions for your assigned cohorts will appear here once scheduled.');
    }
}
