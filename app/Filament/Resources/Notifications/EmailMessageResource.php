<?php

namespace App\Filament\Resources\Notifications;

use App\Filament\Resources\Notifications\EmailMessageResource\Pages;
use App\Filament\Resources\Notifications\EmailMessageResource\RelationManagers;
use App\Models\Notifications\EmailMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmailMessageResource extends Resource
{
    protected static ?string $model = EmailMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled(),
                Forms\Components\Select::make('email_template_id')
                    ->relationship('template', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('recipient_email')
                    ->disabled(),
                Forms\Components\TextInput::make('subject')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recipient_email')->searchable(),
                Tables\Columns\TextColumn::make('subject')->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'queued',
                        'success' => fn ($state) => in_array($state, ['sent', 'fallback_sent']),
                        'danger' => 'failed',
                    ]),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('retry')
                    ->label('Retry Delivery')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (EmailMessage $record) {
                        $record->update(['status' => 'queued']);
                        \App\Jobs\Notifications\SendEmailMessageJob::dispatch($record);
                        \Filament\Notifications\Notification::make()->title('Delivery Queued for Retry')->success()->send();
                    })
                    ->visible(fn (EmailMessage $record) => $record->status === 'failed'),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DeliveryLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailMessages::route('/'),
            'create' => Pages\CreateEmailMessage::route('/create'),
            'view' => Pages\ViewEmailMessage::route('/{record}'),
            'edit' => Pages\EditEmailMessage::route('/{record}/edit'),
        ];
    }
}
