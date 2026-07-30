<?php

namespace App\Filament\Instructor\Resources;

use App\Filament\Instructor\Concerns\ScopesInstructorProfile;
use App\Filament\Instructor\Resources\InstructorLearnerResource\Pages;
use App\Models\Catalog\Enrollment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class InstructorLearnerResource extends Resource
{
    use ScopesInstructorProfile;

    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationGroup = 'Teaching';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Learners';

    protected static ?int $navigationSort = 30;

    protected static bool $shouldSkipAuthorization = true;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('cohort', fn (Builder $query) => $query->where('instructor_profile_id', static::instructorProfileId()))
            ->with(['user', 'product', 'cohort']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Learner')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('product.title')->label('Course')->sortable(),
                Tables\Columns\TextColumn::make('cohort.title')->label('Cohort')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('access_starts_at')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstructorLearners::route('/'),
        ];
    }
}
