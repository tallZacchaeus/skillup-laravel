<?php

namespace App\Filament\Resources;

use App\Enums\DiscountType;
use App\Enums\ScholarshipAwardStatus;
use App\Filament\Resources\ScholarshipAwardResource\Pages;
use App\Models\Catalog\ScholarshipAward;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ScholarshipAwardResource extends Resource
{
    protected static ?string $model = ScholarshipAward::class;

    protected static ?string $navigationGroup = 'Discounts';

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 70;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('scholarship_application_id')->relationship('application', 'name')->searchable()->preload(),
            Forms\Components\Select::make('discount_rule_id')->relationship('discountRule', 'name')->searchable()->preload(),
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->searchable()->preload(),
            Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
            Forms\Components\Select::make('track_id')->relationship('track', 'title')->searchable()->preload(),
            Forms\Components\Select::make('product_id')->relationship('product', 'title')->searchable()->preload(),
            Forms\Components\Select::make('cohort_id')->relationship('cohort', 'title')->searchable()->preload(),
            Forms\Components\Select::make('status')->options(static::statusOptions())->required(),
            Forms\Components\Select::make('discount_type')->options(static::typeOptions())->required(),
            Forms\Components\TextInput::make('discount_value')->numeric()->required()->minValue(0),
            Forms\Components\DateTimePicker::make('starts_at'),
            Forms\Components\DateTimePicker::make('ends_at'),
            Forms\Components\DateTimePicker::make('awarded_at'),
            Forms\Components\KeyValue::make('metadata')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('application.name')->label('Application')->toggleable(),
                Tables\Columns\TextColumn::make('discountRule.name')->label('Rule')->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state)),
                Tables\Columns\TextColumn::make('discount_value')->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(static::statusOptions()),
                Tables\Filters\SelectFilter::make('discount_rule_id')->relationship('discountRule', 'name')->label('Rule'),
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
            'index' => Pages\ListScholarshipAwards::route('/'),
            'create' => Pages\CreateScholarshipAward::route('/create'),
            'edit' => Pages\EditScholarshipAward::route('/{record}/edit'),
        ];
    }

    public static function statusOptions(): array
    {
        return collect(ScholarshipAwardStatus::cases())->mapWithKeys(fn ($status) => [$status->value => Str::headline($status->value)])->all();
    }

    public static function typeOptions(): array
    {
        return collect(DiscountType::cases())->mapWithKeys(fn ($type) => [$type->value => Str::headline($type->value)])->all();
    }
}
