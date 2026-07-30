<?php

namespace App\Filament\Resources;

use App\Enums\ProgramRegistrationStatus;
use App\Filament\Resources\ProgramRegistrationResource\Pages;
use App\Models\Programs\ProgramAttendanceRecord;
use App\Models\Programs\ProgramRegistration;
use App\Services\Programs\ProgramCertificateService;
use App\Services\Programs\ProgramRegistrationService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProgramRegistrationResource extends Resource
{
    protected static ?string $model = ProgramRegistration::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Programs';

    protected static ?string $navigationLabel = 'Registrations';

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('participant_name')
                    ->label('Participant')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn (ProgramRegistration $record) => 'Guardian: '.$record->guardian_name),
                Tables\Columns\TextColumn::make('track.name')->label('Track')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state->value ?? $state))
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        'paid', 'profile_completed', 'enrolled' => 'success',
                        'completed' => 'info',
                        'payment_pending', 'email_verified' => 'warning',
                        'waitlisted' => 'gray',
                        'cancelled', 'abandoned' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-clock'),
                Tables\Columns\IconColumn::make('email_invalid_at')
                    ->label('Bounced')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('profile_completed_at')
                    ->label('Profile')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-circle')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('attendance_records_count')
                    ->counts('attendanceRecords')
                    ->label('Days attended')
                    ->sortable(),
                Tables\Columns\TextColumn::make('certificate.serial')
                    ->label('Certificate')
                    ->fontFamily('mono')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('guardian_whatsapp')->label('WhatsApp')->toggleable(),
                Tables\Columns\TextColumn::make('source')->badge()->color('gray')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('program_edition_id')
                    ->relationship('edition', 'title')
                    ->label('Edition'),
                Tables\Filters\SelectFilter::make('program_edition_track_id')
                    ->relationship('track', 'name')
                    ->label('Track'),
                Tables\Filters\TernaryFilter::make('profile_completed_at')
                    ->label('Profile complete')
                    ->nullable(),
            ])
            ->actions([
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn (ProgramRegistration $record) => 'https://wa.me/'.preg_replace('/\D/', '', (string) $record->guardian_whatsapp)
                        .'?text='.rawurlencode("Hello {$record->guardian_name}, this is the SkillUp team about {$record->participant_name}'s registration for {$record->edition->title}."))
                    ->openUrlInNewTab()
                    ->visible(fn (ProgramRegistration $record) => filled($record->guardian_whatsapp)),

                Tables\Actions\Action::make('record_offline_payment')
                    ->label('Record payment')
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (ProgramRegistration $record) => ! $record->status->isPaidOrBeyond()
                        && ! in_array($record->status, [ProgramRegistrationStatus::Cancelled, ProgramRegistrationStatus::Waitlisted], true))
                    ->form([
                        Forms\Components\TextInput::make('amount')->numeric()->required()->prefix('NGN'),
                        Forms\Components\TextInput::make('reference')->label('Transfer reference')->required(),
                        Forms\Components\Select::make('channel')->options([
                            'bank_transfer' => 'Bank transfer',
                            'cash' => 'Cash',
                            'pos' => 'POS',
                        ])->default('bank_transfer')->required(),
                        Forms\Components\Textarea::make('note')->rows(2),
                    ])
                    ->action(function (ProgramRegistration $record, array $data) {
                        app(ProgramRegistrationService::class)->recordOfflinePayment($record, $data);

                        Notification::make()
                            ->success()
                            ->title('Payment recorded')
                            ->body("{$record->participant_name}'s seat is confirmed.")
                            ->send();
                    }),

                Tables\Actions\Action::make('mark_completed')
                    ->label('Mark completed')
                    ->icon('heroicon-o-academic-cap')
                    ->requiresConfirmation()
                    ->visible(fn (ProgramRegistration $record) => in_array($record->status, [
                        ProgramRegistrationStatus::Paid,
                        ProgramRegistrationStatus::ProfileCompleted,
                        ProgramRegistrationStatus::Enrolled,
                    ], true))
                    ->action(function (ProgramRegistration $record) {
                        $record->update(['status' => ProgramRegistrationStatus::Completed]);

                        Notification::make()
                            ->success()
                            ->title('Marked completed')
                            ->body($record->profile_completed_at
                                ? 'Eligible for a certificate.'
                                : 'Profile still incomplete — certificate stays blocked until the onboarding form is finished.')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_attendance')
                    ->label('Mark attendance')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->form([
                        Forms\Components\DatePicker::make('attended_on')
                            ->label('Session date')
                            ->default(now()->toDateString())
                            ->required(),
                    ])
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, array $data) {
                        $eligible = $records->filter(fn (ProgramRegistration $record) => $record->status->isPaidOrBeyond());

                        $attendedOn = \Illuminate\Support\Carbon::parse($data['attended_on']);

                        $eligible->each(fn (ProgramRegistration $record) => ProgramAttendanceRecord::firstOrCreate(
                            ['program_registration_id' => $record->id, 'attended_on' => $attendedOn],
                            ['present' => true, 'recorded_by_user_id' => auth()->id()],
                        ));

                        Notification::make()
                            ->success()
                            ->title('Attendance recorded')
                            ->body($eligible->count().' participant(s) marked present for '.$data['attended_on']
                                .($records->count() > $eligible->count() ? ' — unpaid registrations were skipped.' : '.'))
                            ->send();
                    }),

                Tables\Actions\BulkAction::make('issue_certificates')
                    ->label('Issue certificates')
                    ->icon('heroicon-o-trophy')
                    ->requiresConfirmation()
                    ->modalDescription('Certificates are only issued to completed registrations with a fully completed onboarding profile. Everything else is skipped.')
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records) {
                        $result = app(ProgramCertificateService::class)->issueEligibleForRegistrations($records);

                        Notification::make()
                            ->{$result['issued'] > 0 ? 'success' : 'warning'}()
                            ->title("{$result['issued']} certificate(s) issued")
                            ->body($result['skipped'] > 0 ? "{$result['skipped']} registration(s) skipped — not completed or profile incomplete." : 'All selected registrations were eligible.')
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProgramRegistrations::route('/'),
        ];
    }
}
