<?php

namespace App\Filament\Resources;

use App\Enums\DiscountRuleStatus;
use App\Enums\DiscountType;
use App\Filament\Resources\DiscountRuleResource\Pages;
use App\Models\Catalog\DiscountRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DiscountRuleResource extends Resource
{
    protected static ?string $model = DiscountRule::class;

    protected static ?string $navigationGroup = 'Discounts';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Rule details')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\Select::make('status')->options(static::statusOptions())->required(),
                Forms\Components\Select::make('type')->options(static::typeOptions())->required(),
                Forms\Components\TextInput::make('value')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('currency')
                    ->required()
                    ->maxLength(3)
                    ->default('NGN'),
                Forms\Components\TextInput::make('minimum_order_amount')
                    ->numeric()
                    ->minValue(0),
            ])->columns(2),
            Forms\Components\Section::make('Eligibility constraints')->schema([
                Forms\Components\Select::make('track_id')->relationship('track', 'title')->searchable()->preload(),
                Forms\Components\Select::make('product_id')->relationship('product', 'title')->searchable()->preload(),
                Forms\Components\Select::make('course_level_id')->relationship('level', 'name')->searchable()->preload(),
                Forms\Components\Select::make('cohort_id')->relationship('cohort', 'title')->searchable()->preload(),
                Forms\Components\DateTimePicker::make('starts_at'),
                Forms\Components\DateTimePicker::make('ends_at'),
                Forms\Components\TextInput::make('usage_limit')->numeric()->minValue(1),
                Forms\Components\TextInput::make('per_email_limit')->numeric()->minValue(0)->default(1),
                Forms\Components\TextInput::make('per_user_limit')->numeric()->minValue(0)->default(1),
            ])->columns(3),
            Forms\Components\Section::make('Checkout behavior')->schema([
                Forms\Components\Toggle::make('requires_code')->default(true),
                Forms\Components\Toggle::make('requires_email_eligibility'),
                Forms\Components\Toggle::make('installment_compatible')->default(true),
                Forms\Components\Toggle::make('stackable'),
                Forms\Components\Toggle::make('is_public'),
                Forms\Components\KeyValue::make('metadata')->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')->sortable(),
                Tables\Columns\IconColumn::make('requires_code')->boolean(),
                Tables\Columns\IconColumn::make('requires_email_eligibility')->boolean(),
                Tables\Columns\TextColumn::make('starts_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(static::statusOptions()),
                Tables\Filters\SelectFilter::make('type')->options(static::typeOptions()),
                Tables\Filters\TernaryFilter::make('requires_email_eligibility'),
                Tables\Filters\TernaryFilter::make('is_public'),
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
            'index' => Pages\ListDiscountRules::route('/'),
            'create' => Pages\CreateDiscountRule::route('/create'),
            'edit' => Pages\EditDiscountRule::route('/{record}/edit'),
        ];
    }

    public static function statusOptions(): array
    {
        return collect(DiscountRuleStatus::cases())->mapWithKeys(fn ($status) => [$status->value => Str::headline($status->value)])->all();
    }

    public static function typeOptions(): array
    {
        return collect(DiscountType::cases())->mapWithKeys(fn ($type) => [$type->value => Str::headline($type->value)])->all();
    }
}
