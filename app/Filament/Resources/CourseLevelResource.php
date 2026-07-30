<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseLevelResource\Pages;
use App\Models\Catalog\CourseLevel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CourseLevelResource extends Resource
{
    protected static ?string $model = CourseLevel::class;

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Levels';

    protected static ?int $navigationSort = 20;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('track_id')
                ->relationship('track', 'title')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
            Forms\Components\TextInput::make('slug')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('rank')
                ->numeric()
                ->required()
                ->default(1),
            Forms\Components\Select::make('status')
                ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                ->required(),
            Forms\Components\TextInput::make('summary')
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('track.title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('rank')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('track_id')->relationship('track', 'title')->label('Track'),
                Tables\Filters\SelectFilter::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive']),
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
            'index' => Pages\ListCourseLevels::route('/'),
            'create' => Pages\CreateCourseLevel::route('/create'),
            'edit' => Pages\EditCourseLevel::route('/{record}/edit'),
        ];
    }
}
