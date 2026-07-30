<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductMoodleMappingResource\Pages;
use App\Models\Catalog\ProductMoodleMapping;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductMoodleMappingResource extends Resource
{
    protected static ?string $model = ProductMoodleMapping::class;

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 90;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')
                ->relationship('product', 'title')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('moodle_connection_id')
                ->relationship('moodleConnection', 'name')
                ->searchable()
                ->preload()
                ->live()
                ->required(),
            Forms\Components\Select::make('moodle_course_id_select')
                ->label('Select Course (Imported)')
                ->options(fn (Forms\Get $get) => \Illuminate\Support\Facades\DB::table('moodle_courses')->where('moodle_connection_id', $get('moodle_connection_id'))->pluck('fullname', 'moodle_course_id'))
                ->live()
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $state ? $set('moodle_course_id', $state) : null),
            Forms\Components\TextInput::make('moodle_course_id')
                ->label('Moodle Course ID')
                ->required()
                ->numeric(),
            Forms\Components\Select::make('moodle_category_id_select')
                ->label('Select Category (Imported)')
                ->options(fn (Forms\Get $get) => \Illuminate\Support\Facades\DB::table('moodle_categories')->where('moodle_connection_id', $get('moodle_connection_id'))->pluck('name', 'moodle_category_id'))
                ->live()
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $state ? $set('moodle_category_id', $state) : null),
            Forms\Components\TextInput::make('moodle_category_id')
                ->label('Moodle Category ID')
                ->numeric(),
            Forms\Components\Select::make('moodle_group_id_select')
                ->label('Select Group (Imported)')
                ->options(fn (Forms\Get $get) => \Illuminate\Support\Facades\DB::table('moodle_groups')->where('moodle_connection_id', $get('moodle_connection_id'))->where('moodle_course_id', $get('moodle_course_id'))->pluck('name', 'moodle_group_id'))
                ->live()
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $state ? $set('moodle_group_id', $state) : null),
            Forms\Components\TextInput::make('moodle_group_id')
                ->label('Moodle Group ID')
                ->numeric(),
            Forms\Components\TextInput::make('moodle_cohort_id')
                ->label('Moodle Cohort ID')
                ->numeric(),
            Forms\Components\Toggle::make('is_primary')->default(true),
            Forms\Components\Toggle::make('sync_enabled')->default(true),
            Forms\Components\DateTimePicker::make('last_synced_at'),
            Forms\Components\KeyValue::make('metadata')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('moodle_course_id')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('moodle_category_id')->toggleable(),
                Tables\Columns\IconColumn::make('is_primary')->boolean(),
                Tables\Columns\IconColumn::make('sync_enabled')->boolean(),
                Tables\Columns\TextColumn::make('last_synced_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')->relationship('product', 'title')->label('Product'),
                Tables\Filters\TernaryFilter::make('is_primary'),
                Tables\Filters\TernaryFilter::make('sync_enabled'),
            ])
            ->actions([
                Tables\Actions\Action::make('markSynced')
                    ->label('Mark synced')
                    ->icon('heroicon-o-arrow-path')
                    ->action(fn (ProductMoodleMapping $record) => $record->update(['last_synced_at' => now()])),
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
            'index' => Pages\ListProductMoodleMappings::route('/'),
            'create' => Pages\CreateProductMoodleMapping::route('/create'),
            'edit' => Pages\EditProductMoodleMapping::route('/{record}/edit'),
        ];
    }
}
