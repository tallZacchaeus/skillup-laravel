<?php

namespace App\Filament\Corporate\Resources;

use App\Filament\Corporate\Concerns\ScopesCorporateAccount;
use App\Filament\Corporate\Resources\CorporateTeamMemberResource\Pages;
use App\Models\Catalog\CorporateLearner;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CorporateTeamMemberResource extends Resource
{
    use ScopesCorporateAccount;

    protected static ?string $model = CorporateLearner::class;

    protected static ?string $navigationGroup = 'Corporate';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Team Members';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldSkipAuthorization = true;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('corporate_account_id', static::corporateAccountIds())
            ->with(['corporateAccount', 'user']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('corporateAccount.name')->label('Company')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('invited_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('accepted_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'invited' => 'Invited',
                        'accepted' => 'Accepted',
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCorporateTeamMembers::route('/'),
        ];
    }
}
