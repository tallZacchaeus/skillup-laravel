<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FutureModuleResource\Pages;
use App\Models\Platform\FutureModule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FutureModuleResource extends Resource
{
    protected static ?string $model = FutureModule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Future Modules';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->required()
                    ->maxLength(255)
                    ->unique(FutureModule::class, 'key', ignoreRecord: true),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('summary')
                    ->maxLength(1000)
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options(self::statusOptions())
                    ->default('planned')
                    ->required(),
                Forms\Components\TextInput::make('module_group')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('public_path')
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\Toggle::make('is_publicly_visible')
                    ->helperText('Public routes still require status = active.'),
                Forms\Components\TagsInput::make('readiness_checks')
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('metadata')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('module_group')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'in_discovery' => 'info',
                        'blocked' => 'danger',
                        'deferred' => 'gray',
                        default => 'warning',
                    }),
                Tables\Columns\IconColumn::make('is_publicly_visible')
                    ->boolean()
                    ->label('Public'),
                Tables\Columns\TextColumn::make('public_path')
                    ->placeholder('No public route'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(self::statusOptions()),
                Tables\Filters\SelectFilter::make('module_group')
                    ->options(fn () => FutureModule::query()->pluck('module_group', 'module_group')->all()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (FutureModule $record) => $record->update([
                        'status' => 'active',
                        'is_publicly_visible' => filled($record->public_path),
                    ]))
                    ->visible(fn (FutureModule $record): bool => $record->status !== 'active'),
                Tables\Actions\Action::make('defer')
                    ->icon('heroicon-o-pause-circle')
                    ->color('gray')
                    ->action(fn (FutureModule $record) => $record->update([
                        'status' => 'deferred',
                        'is_publicly_visible' => false,
                    ]))
                    ->visible(fn (FutureModule $record): bool => $record->status !== 'deferred'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFutureModules::route('/'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'planned' => 'Planned',
            'in_discovery' => 'In Discovery',
            'active' => 'Active',
            'blocked' => 'Blocked',
            'deferred' => 'Deferred',
        ];
    }
}
