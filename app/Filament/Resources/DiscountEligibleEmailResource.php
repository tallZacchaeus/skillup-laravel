<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountEligibleEmailResource\Pages;
use App\Models\Catalog\DiscountEligibleEmail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DiscountEligibleEmailResource extends Resource
{
    protected static ?string $model = DiscountEligibleEmail::class;

    protected static ?string $navigationGroup = 'Discounts';

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 40;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('discount_eligibility_list_id')
                ->relationship('eligibilityList', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
            Forms\Components\TextInput::make('name')->maxLength(255),
            Forms\Components\Select::make('status')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ])
                ->default('active')
                ->required(),
            Forms\Components\KeyValue::make('metadata')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('eligibilityList.name')->label('List')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('eligibilityList.discountRule.name')->label('Rule')->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('discount_eligibility_list_id')->relationship('eligibilityList', 'name')->label('List'),
                Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscountEligibleEmails::route('/'),
            'create' => Pages\CreateDiscountEligibleEmail::route('/create'),
            'edit' => Pages\EditDiscountEligibleEmail::route('/{record}/edit'),
        ];
    }
}
