<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExportRequestResource\Pages;
use App\Models\Operations\ExportRequest;
use App\Services\Operations\ExportService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExportRequestResource extends Resource
{
    protected static ?string $model = ExportRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationLabel = 'Export Center';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('export_type')
                    ->options(ExportService::exportTypeOptions())
                    ->required(),
                Forms\Components\KeyValue::make('filters')
                    ->helperText('Reserved for date ranges, cohorts, products, and other filters.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('export_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'failed' => 'danger',
                        'processing' => 'warning',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('row_count')
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_path')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('export_type')
                    ->options(ExportService::exportTypeOptions()),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'queued' => 'Queued',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('generate')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (ExportRequest $record): void {
                        app(ExportService::class)->generate($record);

                        Notification::make()
                            ->title('Export generated')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageExportRequests::route('/'),
        ];
    }
}
