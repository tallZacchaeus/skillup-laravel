<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountEligibilityListResource\Pages;
use App\Models\Catalog\DiscountEligibilityList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DiscountEligibilityListResource extends Resource
{
    protected static ?string $model = DiscountEligibilityList::class;

    protected static ?string $navigationGroup = 'Discounts';

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?int $navigationSort = 30;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('discount_rule_id')
                ->relationship('discountRule', 'name')
                ->searchable()
                ->preload(),
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
            Forms\Components\TextInput::make('source_filename')->disabled()->dehydrated(false),
            Forms\Components\TextInput::make('total_emails')->numeric()->disabled()->dehydrated(false),
            Forms\Components\KeyValue::make('metadata')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('discountRule.name')->label('Rule')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('total_emails')->sortable(),
                Tables\Columns\TextColumn::make('source_filename')->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
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
            'index' => Pages\ListDiscountEligibilityLists::route('/'),
            'create' => Pages\CreateDiscountEligibilityList::route('/create'),
            'edit' => Pages\EditDiscountEligibilityList::route('/{record}/edit'),
        ];
    }
}
