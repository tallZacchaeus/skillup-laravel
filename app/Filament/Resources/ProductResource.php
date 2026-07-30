<?php

namespace App\Filament\Resources;

use App\Enums\ProductStatus;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Catalog\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 40;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Product details')->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('subtitle')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('track_id')
                    ->relationship('track', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('course_level_id')
                    ->relationship('level', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('cohort_id')
                    ->relationship('cohort', 'title')
                    ->searchable()
                    ->preload(),
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
                Forms\Components\Toggle::make('unlimited_enrollment')
                    ->default(true),
                Forms\Components\TextInput::make('enrollment_cap')
                    ->numeric()
                    ->minValue(1),
                Forms\Components\Toggle::make('is_featured'),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Forms\Components\DateTimePicker::make('published_at'),
            ])->columns(2),
            Forms\Components\Section::make('Sales content')->schema([
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->rows(6)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('promo_video_url')
                    ->label('Intro / preview video URL')
                    ->url()
                    ->helperText('YouTube, Vimeo, or a direct MP4 link. Shown on the course page.')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('students_count')
                    ->label('Learners enrolled (social proof)')
                    ->numeric()
                    ->default(0),
                Forms\Components\TagsInput::make('outcomes')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('tags')
                    ->label('Skills / tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    ])
                    ->helperText('Filterable skills shown on the course card and catalogue facets.')
                    ->columnSpanFull(),
                Forms\Components\Group::make()
                    ->columns(1)
                    ->columnSpanFull()
                    ->statePath('relevance')
                    ->schema([
                        Forms\Components\Textarea::make('demandNote')
                            ->label('Why this course matters (market demand)')
                            ->rows(3),
                        Forms\Components\TagsInput::make('audience')
                            ->label('Who this is for'),
                        Forms\Components\Repeater::make('stats')
                            ->label('Relevance stats')
                            ->schema([
                                Forms\Components\TextInput::make('label')->required(),
                                Forms\Components\TextInput::make('value')->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(0),
                    ]),
                Forms\Components\Repeater::make('syllabus')
                    ->schema([
                        Forms\Components\TextInput::make('week')->required(),
                        Forms\Components\TextInput::make('title')->required(),
                    ])
                    ->defaultItems(3)
                    ->columnSpanFull(),
                Forms\Components\TagsInput::make('requirements')
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('metadata')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['track', 'level', 'defaultPrice']))
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('track.title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('level.name')->label('Level')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('defaultPrice.amount')
                    ->label('Price')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
                Tables\Columns\TextColumn::make('published_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(static::statusOptions()),
                Tables\Filters\SelectFilter::make('track_id')->relationship('track', 'title')->label('Track'),
                Tables\Filters\SelectFilter::make('course_level_id')->relationship('level', 'name')->label('Level'),
                Tables\Filters\Filter::make('sold_out')
                    ->query(fn (Builder $query) => $query->where('status', ProductStatus::SoldOut->value)),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Product $record) => route('courses.products.show', [$record->track->slug, $record->slug]))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function statusOptions(): array
    {
        return collect(ProductStatus::cases())
            ->mapWithKeys(fn (ProductStatus $status) => [$status->value => Str::headline($status->value)])
            ->all();
    }
}
