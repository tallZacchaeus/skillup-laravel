<?php

namespace App\Filament\Resources\Notifications;

use App\Filament\Resources\Notifications\WhatsappMessageResource\Pages;
use App\Filament\Resources\Notifications\WhatsappMessageResource\RelationManagers;
use App\Models\Notifications\WhatsappMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WhatsappMessageResource extends Resource
{
    protected static ?string $model = WhatsappMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('recipient_phone')
                    ->disabled(),
                Forms\Components\TextInput::make('template_name')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recipient_phone')->searchable(),
                Tables\Columns\TextColumn::make('template_name')->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'queued',
                        'success' => 'sent',
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
                    ->action(function (WhatsappMessage $record) {
                        $record->update(['status' => 'queued']);
                        \App\Jobs\Notifications\SendWhatsappMessageJob::dispatch($record);
                        \Filament\Notifications\Notification::make()->title('Delivery Queued for Retry')->success()->send();
                    })
                    ->visible(fn (WhatsappMessage $record) => $record->status === 'failed'),
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
            'index' => Pages\ListWhatsappMessages::route('/'),
            'create' => Pages\CreateWhatsappMessage::route('/create'),
            'view' => Pages\ViewWhatsappMessage::route('/{record}'),
            'edit' => Pages\EditWhatsappMessage::route('/{record}/edit'),
        ];
    }
}
