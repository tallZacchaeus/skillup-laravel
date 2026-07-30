<?php

namespace App\Filament\Resources;

use App\Enums\EnrollmentStatus;
use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Catalog\Enrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationGroup = 'LMS Integration';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 15;

    protected static bool $shouldSkipAuthorization = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('product_id')
                ->relationship('product', 'title')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('cohort_id')
                ->relationship('cohort', 'title')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('status')
                ->options(collect(EnrollmentStatus::cases())->mapWithKeys(fn ($status) => [$status->value => ucfirst($status->value)])->toArray())
                ->required(),
            Forms\Components\TextInput::make('moodle_user_id')
                ->maxLength(255),
            Forms\Components\TextInput::make('moodle_course_id')
                ->maxLength(255),
            Forms\Components\DateTimePicker::make('provisioned_at'),
            Forms\Components\Textarea::make('failed_reason')
                ->maxLength(65535)
                ->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('product.title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cohort.title')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (EnrollmentStatus $state): string => match ($state) {
                        EnrollmentStatus::Active => 'success',
                        EnrollmentStatus::Pending => 'warning',
                        EnrollmentStatus::Failed => 'danger',
                        EnrollmentStatus::Suspended => 'gray',
                        default => 'secondary',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('moodle_user_id')->label('Moodle User ID')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('moodle_course_id')->label('Moodle Course ID')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('provisioned_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('failed_reason')->limit(30)->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(EnrollmentStatus::cases())->mapWithKeys(fn ($status) => [$status->value => ucfirst($status->value)])->toArray()),
            ])
            ->actions([
                Tables\Actions\Action::make('retryMoodle')
                    ->label('Retry Moodle')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn (Enrollment $record) => in_array($record->status, [EnrollmentStatus::Failed, EnrollmentStatus::Pending, EnrollmentStatus::Partial]))
                    ->action(function (Enrollment $record) {
                        \App\Jobs\Lms\EnrollUserInMoodleJob::dispatch($record);
                        Notification::make()
                            ->title('Job Dispatched')
                            ->body('Moodle enrollment provisioning job has been dispatched.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnrollments::route('/'),
        ];
    }
}
