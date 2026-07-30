<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportTicketResource\Pages;
use App\Filament\Resources\SupportTicketResource\RelationManagers;
use App\Models\Support\SupportTicket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static ?string $recordTitleAttribute = 'subject';

    protected static ?string $navigationIcon = 'heroicon-o-lifebuoy';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationLabel = 'Support Tickets';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('subject')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('corporate_account_id')
                    ->relationship('corporateAccount', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('assigned_to_id')
                    ->relationship('assignee', 'email')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('requester_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('requester_email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\Select::make('category')
                    ->options(self::categoryOptions())
                    ->default('general')
                    ->required(),
                Forms\Components\Select::make('priority')
                    ->options(self::priorityOptions())
                    ->default('normal')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(self::statusOptions())
                    ->default('open')
                    ->required(),
                Forms\Components\Select::make('source')
                    ->options(self::sourceOptions())
                    ->default('admin')
                    ->required(),
                Forms\Components\KeyValue::make('metadata')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('last_activity_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('requester_email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge(),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'normal' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'info',
                        'pending' => 'warning',
                        'resolved', 'closed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('assignee.email')
                    ->label('Assignee')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_activity_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(self::statusOptions()),
                Tables\Filters\SelectFilter::make('priority')->options(self::priorityOptions()),
                Tables\Filters\SelectFilter::make('category')->options(self::categoryOptions()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (SupportTicket $record) => $record->update(['status' => 'resolved']))
                    ->visible(fn (SupportTicket $record): bool => ! in_array($record->status, ['resolved', 'closed'], true)),
                Tables\Actions\Action::make('reopen')
                    ->icon('heroicon-o-arrow-path')
                    ->action(fn (SupportTicket $record) => $record->update(['status' => 'open']))
                    ->visible(fn (SupportTicket $record): bool => in_array($record->status, ['resolved', 'closed'], true)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportTickets::route('/'),
            'create' => Pages\CreateSupportTicket::route('/create'),
            'view' => Pages\ViewSupportTicket::route('/{record}'),
            'edit' => Pages\EditSupportTicket::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function categoryOptions(): array
    {
        return [
            'general' => 'General',
            'billing' => 'Billing',
            'lms' => 'LMS Access',
            'technical' => 'Technical',
            'corporate' => 'Corporate',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function priorityOptions(): array
    {
        return [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'open' => 'Open',
            'pending' => 'Pending',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sourceOptions(): array
    {
        return [
            'learner' => 'Learner Portal',
            'corporate' => 'Corporate Portal',
            'admin' => 'Admin',
            'public' => 'Public',
        ];
    }
}
