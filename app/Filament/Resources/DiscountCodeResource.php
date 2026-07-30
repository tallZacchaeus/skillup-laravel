<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountCodeResource\Pages;
use App\Models\Catalog\DiscountCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DiscountCodeResource extends Resource
{
    protected static ?string $model = DiscountCode::class;

    protected static ?string $navigationGroup = 'Discounts';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 20;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('discount_rule_id')
                ->relationship('discountRule', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('code')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            Forms\Components\Select::make('visibility')
                ->options([
                    'public' => 'Public',
                    'private' => 'Private',
                    'single_use' => 'Single use',
                ])
                ->default('private')
                ->required(),
            Forms\Components\TextInput::make('max_redemptions')->numeric()->minValue(1),
            Forms\Components\TextInput::make('redeemed_count')->numeric()->disabled()->dehydrated(false),
            Forms\Components\DateTimePicker::make('starts_at'),
            Forms\Components\DateTimePicker::make('ends_at'),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\KeyValue::make('metadata')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('discountRule.name')->label('Rule')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('visibility')->badge()->sortable(),
                Tables\Columns\TextColumn::make('max_redemptions')->sortable(),
                Tables\Columns\TextColumn::make('redeemed_count')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('discount_rule_id')->relationship('discountRule', 'name')->label('Rule'),
                Tables\Filters\SelectFilter::make('visibility')->options([
                    'public' => 'Public',
                    'private' => 'Private',
                    'single_use' => 'Single use',
                ]),
                Tables\Filters\TernaryFilter::make('is_active'),
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
            'index' => Pages\ListDiscountCodes::route('/'),
            'create' => Pages\CreateDiscountCode::route('/create'),
            'edit' => Pages\EditDiscountCode::route('/{record}/edit'),
        ];
    }
}
