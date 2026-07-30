<?php

namespace App\Filament\Corporate\Resources;

use App\Filament\Corporate\Concerns\ScopesCorporateAccount;
use App\Filament\Corporate\Resources\CorporateEnrollmentResource\Pages;
use App\Models\Catalog\Enrollment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CorporateEnrollmentResource extends Resource
{
    use ScopesCorporateAccount;

    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationGroup = 'Corporate';

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Enrollment Status';

    protected static ?int $navigationSort = 20;

    protected static bool $shouldSkipAuthorization = true;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('corporate_account_id', static::corporateAccountIds())
            ->with(['user', 'product.track', 'cohort']);
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
                Tables\Columns\TextColumn::make('product.title')->label('Course')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cohort.title')->label('Cohort')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
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
            'index' => Pages\ListCorporateEnrollments::route('/'),
        ];
    }
}
