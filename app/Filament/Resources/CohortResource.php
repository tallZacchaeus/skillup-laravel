<?php

namespace App\Filament\Resources;

use App\Enums\CohortStatus;
use App\Filament\Resources\CohortResource\Pages;
use App\Models\Catalog\Cohort;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CohortResource extends Resource
{
    protected static ?string $model = Cohort::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 30;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Cohort details')->schema([
                Forms\Components\Select::make('track_id')
                    ->relationship('track', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('course_level_id')
                    ->relationship('level', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('instructor_profile_id')
                    ->relationship('instructorProfile', 'title')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('status')
                    ->options(static::statusOptions())
                    ->required(),
                Forms\Components\Select::make('delivery_mode')
                    ->options([
                        'online' => 'Online',
                        'onsite' => 'Onsite',
                        'hybrid' => 'Hybrid',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('timezone')
                    ->required()
                    ->default('Africa/Lagos'),
                Forms\Components\TextInput::make('max_learners')
                    ->numeric()
                    ->minValue(1),
                Forms\Components\TextInput::make('enrolled_count')
                    ->numeric()
                    ->default(0),
            ])->columns(2),
            Forms\Components\Section::make('Schedule')->schema([
                Forms\Components\DateTimePicker::make('enrollment_opens_at'),
                Forms\Components\DateTimePicker::make('enrollment_closes_at'),
                Forms\Components\DateTimePicker::make('starts_at'),
                Forms\Components\DateTimePicker::make('ends_at'),
            ])->columns(2),
            Forms\Components\KeyValue::make('metadata'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('track.title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('level.name')->label('Level')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('starts_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('enrolled_count')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(static::statusOptions()),
                Tables\Filters\SelectFilter::make('track_id')->relationship('track', 'title')->label('Track'),
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
            'index' => Pages\ListCohorts::route('/'),
            'create' => Pages\CreateCohort::route('/create'),
            'edit' => Pages\EditCohort::route('/{record}/edit'),
        ];
    }

    private static function statusOptions(): array
    {
        return collect(CohortStatus::cases())
            ->mapWithKeys(fn (CohortStatus $status) => [$status->value => Str::headline($status->value)])
            ->all();
    }
}
