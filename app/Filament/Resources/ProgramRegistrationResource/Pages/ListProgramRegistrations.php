<?php

namespace App\Filament\Resources\ProgramRegistrationResource\Pages;

use App\Enums\ProgramRegistrationStatus;
use App\Filament\Resources\ProgramRegistrationResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProgramRegistrations extends ListRecords
{
    protected static string $resource = ProgramRegistrationResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'in_funnel' => Tab::make('In funnel')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    ProgramRegistrationStatus::Started->value,
                    ProgramRegistrationStatus::EmailVerified->value,
                    ProgramRegistrationStatus::PaymentPending->value,
                ])),
            'paid' => Tab::make('Paid')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ProgramRegistrationStatus::seatConsumingValues())),
            'profile_incomplete' => Tab::make('Profile incomplete')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereIn('status', ProgramRegistrationStatus::seatConsumingValues())
                    ->whereNull('profile_completed_at')),
            'waitlisted' => Tab::make('Waitlist')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ProgramRegistrationStatus::Waitlisted->value)),
            'email_invalid' => Tab::make('Email bounced')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('email_invalid_at')),
        ];
    }
}
