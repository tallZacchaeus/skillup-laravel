<?php

namespace App\Filament\Learner\Widgets;

use App\Enums\EnrollmentStatus;
use App\Models\Catalog\CohortSession;
use App\Models\Catalog\Enrollment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Str;

class LearnerUpcomingSessions extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Upcoming live sessions';

    public function table(Table $table): Table
    {
        $cohortIds = Enrollment::where('user_id', auth()->id())
            ->whereIn('status', [EnrollmentStatus::Active->value, EnrollmentStatus::Pending->value])
            ->whereNotNull('cohort_id')
            ->pluck('cohort_id');

        return $table
            ->query(
                CohortSession::query()
                    ->whereIn('cohort_id', $cohortIds)
                    ->where('starts_at', '>=', now())
                    ->orderBy('starts_at')
                    ->limit(5),
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('title')->weight('semibold'),
                Tables\Columns\TextColumn::make('cohort.title')->label('Cohort'),
                Tables\Columns\TextColumn::make('starts_at')->label('Starts')->dateTime('D, M j · g:i A'),
                Tables\Columns\TextColumn::make('delivery_mode')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline((string) $state)),
                Tables\Columns\TextColumn::make('meeting_url')
                    ->label('Join')
                    ->formatStateUsing(fn ($state) => $state ? 'Open meeting link' : '—')
                    ->url(fn (CohortSession $record) => $record->meeting_url ?: null, shouldOpenInNewTab: true)
                    ->color('primary'),
            ])
            ->emptyStateHeading('No upcoming sessions')
            ->emptyStateDescription('Live session times will appear here once your cohort schedule is published.');
    }
}
