<?php

namespace App\Filament\Learner\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class LearnerNotifications extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Account';

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?int $navigationSort = 40;

    protected static string $view = 'filament.learner.pages.notifications';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Auth::user()->notifications()->getQuery()
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('data.subject')
                    ->label('Subject')
                    ->searchable(),
                TextColumn::make('data.message')
                    ->label('Message')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('read_at')
                    ->label('Read Status')
                    ->dateTime()
                    ->placeholder('Unread'),
            ])
            ->actions([
                Action::make('markAsRead')
                    ->label('Mark as Read')
                    ->icon('heroicon-o-check')
                    ->action(fn ($record) => $record->markAsRead())
                    ->visible(fn ($record) => $record->read_at === null),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}
