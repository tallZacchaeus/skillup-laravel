<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductVisibilityRuleResource\Pages;
use App\Models\Catalog\ProductVisibilityRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductVisibilityRuleResource extends Resource
{
    protected static ?string $model = ProductVisibilityRule::class;

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationIcon = 'heroicon-o-eye';

    protected static ?int $navigationSort = 80;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')
                ->relationship('product', 'title')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('rule_type')
                ->options([
                    'public' => 'Public',
                    'email_domain' => 'Email domain',
                    'email_allowlist' => 'Email allowlist',
                    'corporate_account' => 'Corporate account',
                    'cohort_window' => 'Cohort window',
                    'invite_only' => 'Invite only',
                ])
                ->searchable()
                ->required(),
            Forms\Components\Select::make('operator')
                ->options([
                    'equals' => 'Equals',
                    'not_equals' => 'Does not equal',
                    'in' => 'In list',
                    'not_in' => 'Not in list',
                    'contains' => 'Contains',
                ])
                ->default('equals')
                ->required(),
            Forms\Components\KeyValue::make('value')
                ->helperText('Store rule values such as allowed domains, emails, or corporate account identifiers.')
                ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('product.title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('rule_type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('operator')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('starts_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')->relationship('product', 'title')->label('Product'),
                Tables\Filters\SelectFilter::make('rule_type')
                    ->options([
                        'public' => 'Public',
                        'email_domain' => 'Email domain',
                        'email_allowlist' => 'Email allowlist',
                        'corporate_account' => 'Corporate account',
                        'cohort_window' => 'Cohort window',
                        'invite_only' => 'Invite only',
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
            'index' => Pages\ListProductVisibilityRules::route('/'),
            'create' => Pages\CreateProductVisibilityRule::route('/create'),
            'edit' => Pages\EditProductVisibilityRule::route('/{record}/edit'),
        ];
    }
}
