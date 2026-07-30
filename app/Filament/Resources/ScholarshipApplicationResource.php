<?php

namespace App\Filament\Resources;

use App\Enums\DiscountType;
use App\Enums\ScholarshipApplicationStatus;
use App\Filament\Resources\ScholarshipApplicationResource\Pages;
use App\Models\Catalog\ScholarshipApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ScholarshipApplicationResource extends Resource
{
    protected static ?string $model = ScholarshipApplication::class;

    protected static ?string $navigationGroup = 'Discounts';

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?int $navigationSort = 60;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Applicant')->schema([
                Forms\Components\Select::make('user_id')->relationship('user', 'name')->searchable()->preload(),
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
                Forms\Components\TextInput::make('phone')->maxLength(255),
                Forms\Components\TextInput::make('country')->maxLength(255),
                Forms\Components\Textarea::make('reason')->columnSpanFull(),
            ])->columns(2),
            Forms\Components\Section::make('Request')->schema([
                Forms\Components\Select::make('track_id')->relationship('track', 'title')->searchable()->preload(),
                Forms\Components\Select::make('product_id')->relationship('product', 'title')->searchable()->preload(),
                Forms\Components\Select::make('cohort_id')->relationship('cohort', 'title')->searchable()->preload(),
                Forms\Components\Select::make('status')->options(static::statusOptions())->required(),
                Forms\Components\Select::make('requested_discount_type')->options(static::typeOptions())->required(),
                Forms\Components\TextInput::make('requested_discount_value')->numeric()->required()->minValue(0),
                Forms\Components\DateTimePicker::make('reviewed_at'),
                Forms\Components\KeyValue::make('metadata')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('product.title')->label('Product')->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('requested_discount_type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state)),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(static::statusOptions()),
                Tables\Filters\SelectFilter::make('product_id')->relationship('product', 'title')->label('Product'),
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
            'index' => Pages\ListScholarshipApplications::route('/'),
            'create' => Pages\CreateScholarshipApplication::route('/create'),
            'edit' => Pages\EditScholarshipApplication::route('/{record}/edit'),
        ];
    }

    public static function statusOptions(): array
    {
        return collect(ScholarshipApplicationStatus::cases())->mapWithKeys(fn ($status) => [$status->value => Str::headline($status->value)])->all();
    }

    public static function typeOptions(): array
    {
        return collect(DiscountType::cases())->mapWithKeys(fn ($type) => [$type->value => Str::headline($type->value)])->all();
    }
}
