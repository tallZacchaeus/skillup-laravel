<?php

namespace App\Filament\Instructor\Resources;

use App\Filament\Instructor\Concerns\ScopesInstructorProfile;
use App\Filament\Instructor\Resources\InstructorSessionResource\Pages;
use App\Models\Catalog\CohortSession;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class InstructorSessionResource extends Resource
{
    use ScopesInstructorProfile;

    protected static ?string $model = CohortSession::class;

    protected static ?string $navigationGroup = 'Teaching';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Sessions And Notes';

    protected static ?int $navigationSort = 20;

    protected static bool $shouldSkipAuthorization = true;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('cohort', fn (Builder $query) => $query->where('instructor_profile_id', static::instructorProfileId()))
            ->with(['cohort.track']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cohort.title')->label('Cohort')->sortable(),
                Tables\Columns\TextColumn::make('cohort.track.title')->label('Track')->sortable(),
                Tables\Columns\TextColumn::make('delivery_mode')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline((string) $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('meeting_url')->url(fn (?string $state) => $state)->openUrlInNewTab(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('delivery_mode')
                    ->options([
                        'online' => 'Online',
                        'onsite' => 'Onsite',
                        'hybrid' => 'Hybrid',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstructorSessions::route('/'),
        ];
    }
}
