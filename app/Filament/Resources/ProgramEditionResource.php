<?php

namespace App\Filament\Resources;

use App\Enums\ProgramEditionStatus;
use App\Filament\Resources\ProgramEditionResource\Pages;
use App\Models\Programs\ProgramEdition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProgramEditionResource extends Resource
{
    protected static ?string $model = ProgramEdition::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Programs';

    protected static ?string $navigationLabel = 'Editions';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Edition')->columnSpanFull()->tabs([
                    Forms\Components\Tabs\Tab::make('Details')->schema([
                        Forms\Components\Select::make('program_id')
                            ->relationship('program', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('year')->numeric()->required(),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->helperText('e.g. "2026" — appears in archive URLs.'),
                        Forms\Components\TextInput::make('title')->required()->maxLength(160),
                        Forms\Components\TextInput::make('theme')->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options(collect(ProgramEditionStatus::cases())->mapWithKeys(
                                fn (ProgramEditionStatus $status) => [$status->value => Str::headline($status->value)],
                            ))
                            ->required(),
                        Forms\Components\DatePicker::make('starts_on'),
                        Forms\Components\DatePicker::make('ends_on'),
                        Forms\Components\TextInput::make('schedule_text')->maxLength(120),
                        Forms\Components\Select::make('delivery_mode')->options([
                            'in_person' => 'In person',
                            'online' => 'Online',
                            'hybrid' => 'Hybrid',
                        ])->default('in_person'),
                        Forms\Components\TextInput::make('venue_name')->maxLength(160),
                        Forms\Components\TextInput::make('venue_address')->maxLength(255),
                        Forms\Components\TextInput::make('venue_map_url')->url()->maxLength(255),
                    ])->columns(2),

                    Forms\Components\Tabs\Tab::make('Registration & payment')->schema([
                        Forms\Components\TextInput::make('capacity_total')->numeric(),
                        Forms\Components\Select::make('payment_mode')->options([
                            'immediate' => 'Pay-first (default)',
                            'after_confirmation' => 'After team confirmation',
                        ])->default('immediate'),
                        Forms\Components\DatePicker::make('age_reference_date')
                            ->helperText('Ages are computed as of this date. Leave empty to use the start date.'),
                        Forms\Components\TextInput::make('seat_hold_minutes')->numeric()->default(45)
                            ->helperText('How long a checkout hold blocks a seat.'),
                        Forms\Components\TextInput::make('safeguarding_retention_months')->numeric()->default(6)
                            ->helperText('Months after the edition ends before medical/pickup data is auto-purged.'),
                        Forms\Components\Toggle::make('allow_installments'),
                        Forms\Components\TextInput::make('terms_url')->url(),
                        Forms\Components\Textarea::make('refund_policy')->rows(3)->columnSpanFull(),
                        Forms\Components\Repeater::make('registration_fields')
                            ->columnSpanFull()
                            ->schema([
                                Forms\Components\TextInput::make('key')->required()
                                    ->helperText('Machine key, e.g. tshirt_size'),
                                Forms\Components\TextInput::make('label')->required(),
                                Forms\Components\Select::make('type')->options([
                                    'text' => 'Text',
                                    'textarea' => 'Long text',
                                    'select' => 'Select',
                                ])->default('text')->required(),
                                Forms\Components\TagsInput::make('options')
                                    ->helperText('For selects.'),
                                Forms\Components\Toggle::make('required')
                                    ->helperText('Required fields gate the certificate.'),
                            ])
                            ->columns(2)
                            ->default([]),
                    ])->columns(2),

                    Forms\Components\Tabs\Tab::make('Page content')->schema([
                        Forms\Components\TextInput::make('seo_title')->maxLength(160),
                        Forms\Components\Textarea::make('seo_description')->rows(2),
                        Forms\Components\TextInput::make('hero_image_path')
                            ->helperText('e.g. /images/summer-ai-hero.jpg'),
                        Forms\Components\TextInput::make('contact_whatsapp'),
                        Forms\Components\TextInput::make('contact_email')->email(),
                        Forms\Components\Builder::make('content')
                            ->columnSpanFull()
                            ->collapsible()
                            ->blocks([
                                Forms\Components\Builder\Block::make('quick_facts')->schema([
                                    Forms\Components\Repeater::make('items')->schema([
                                        Forms\Components\TextInput::make('label')->required(),
                                        Forms\Components\TextInput::make('value')->required(),
                                    ])->columns(2),
                                ]),
                                Forms\Components\Builder\Block::make('overview')->schema([
                                    Forms\Components\TextInput::make('title'),
                                    Forms\Components\Textarea::make('body')->rows(4)->required(),
                                ]),
                                Forms\Components\Builder\Block::make('why')->schema([
                                    Forms\Components\TextInput::make('title'),
                                    Forms\Components\TextInput::make('subtitle'),
                                    Forms\Components\Repeater::make('items')->schema([
                                        Forms\Components\TextInput::make('title')->required(),
                                        Forms\Components\Textarea::make('text')->rows(2)->required(),
                                    ]),
                                ]),
                                Forms\Components\Builder\Block::make('tracks')->schema([
                                    Forms\Components\TextInput::make('title'),
                                    Forms\Components\TextInput::make('subtitle'),
                                ]),
                                Forms\Components\Builder\Block::make('journey')->schema([
                                    Forms\Components\TextInput::make('title'),
                                    Forms\Components\Repeater::make('items')->schema([
                                        Forms\Components\TextInput::make('week')->required(),
                                        Forms\Components\Textarea::make('focus')->rows(2)->required(),
                                    ]),
                                ]),
                                Forms\Components\Builder\Block::make('includes')->schema([
                                    Forms\Components\TextInput::make('title'),
                                    Forms\Components\TextInput::make('subtitle'),
                                    Forms\Components\TagsInput::make('items'),
                                ]),
                                Forms\Components\Builder\Block::make('team')->schema([
                                    Forms\Components\TextInput::make('title'),
                                    Forms\Components\Repeater::make('items')->schema([
                                        Forms\Components\TextInput::make('role')->required(),
                                        Forms\Components\Textarea::make('text')->rows(2)->required(),
                                    ]),
                                ]),
                                Forms\Components\Builder\Block::make('faqs')->schema([
                                    Forms\Components\TextInput::make('title'),
                                    Forms\Components\Repeater::make('items')->schema([
                                        Forms\Components\TextInput::make('question')->required(),
                                        Forms\Components\Textarea::make('answer')->rows(3)->required(),
                                    ]),
                                ]),
                                Forms\Components\Builder\Block::make('gallery')->schema([
                                    Forms\Components\TextInput::make('title'),
                                    Forms\Components\TextInput::make('subtitle'),
                                    Forms\Components\Repeater::make('images')->schema([
                                        Forms\Components\TextInput::make('src')->required()
                                            ->helperText('e.g. /storage/programs/2026/showcase-1.jpg'),
                                        Forms\Components\TextInput::make('alt'),
                                    ])->columns(2),
                                ]),
                                Forms\Components\Builder\Block::make('venue')->schema([
                                    Forms\Components\TextInput::make('title'),
                                    Forms\Components\Textarea::make('note')->rows(2),
                                ]),
                                Forms\Components\Builder\Block::make('event')->schema([
                                    Forms\Components\TextInput::make('title'),
                                    Forms\Components\TextInput::make('subtitle'),
                                    Forms\Components\TextInput::make('href')->required()
                                        ->helperText('e.g. /events/showcase-day-2026'),
                                    Forms\Components\TextInput::make('cta'),
                                ]),
                                Forms\Components\Builder\Block::make('cta')->schema([
                                    Forms\Components\TextInput::make('title'),
                                    Forms\Components\TextInput::make('subtitle'),
                                    Forms\Components\TextInput::make('cta'),
                                ]),
                            ]),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('program.name')->sortable(),
                Tables\Columns\TextColumn::make('title')->searchable()->weight('semibold'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        ProgramEditionStatus::RegistrationOpen->value => 'success',
                        ProgramEditionStatus::SoldOut->value, ProgramEditionStatus::Running->value => 'warning',
                        ProgramEditionStatus::Draft->value => 'gray',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('starts_on')->date()->sortable(),
                Tables\Columns\TextColumn::make('registrations_count')->counts('registrations')->label('Registrations'),
            ])
            ->defaultSort('starts_on', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProgramEditionResource\RelationManagers\TracksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProgramEditions::route('/'),
            'create' => Pages\CreateProgramEdition::route('/create'),
            'edit' => Pages\EditProgramEdition::route('/{record}/edit'),
        ];
    }
}
