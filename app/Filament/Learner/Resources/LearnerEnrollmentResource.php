<?php

namespace App\Filament\Learner\Resources;

use App\Filament\Learner\Resources\LearnerEnrollmentResource\Pages;
use App\Models\Catalog\Enrollment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class LearnerEnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationGroup = 'Learning';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'My Courses';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldSkipAuthorization = true;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->with(['product.track', 'cohort']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.title')->label('Course')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('product.track.title')->label('Track')->sortable(),
                Tables\Columns\TextColumn::make('cohort.title')->label('Cohort')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('access_starts_at')->date()->sortable(),
                Tables\Columns\TextColumn::make('access_ends_at')->date()->sortable(),
                Tables\Columns\TextColumn::make('provisioned_at')->dateTime()->sortable(),
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
            'index' => Pages\ListLearnerEnrollments::route('/'),
        ];
    }
}
