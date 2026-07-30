<?php

namespace App\Filament\Widgets;

use App\Enums\ProgramEditionStatus;
use App\Enums\ProgramRegistrationStatus;
use App\Models\Programs\ProgramEdition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProgramFunnelOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 6;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $edition = ProgramEdition::query()
            ->whereIn('status', [
                ProgramEditionStatus::RegistrationOpen->value,
                ProgramEditionStatus::SoldOut->value,
                ProgramEditionStatus::Running->value,
            ])
            ->with('tracks')
            ->orderByDesc('year')
            ->first();

        if (! $edition) {
            return [];
        }

        $registrations = $edition->registrations();
        $started = (clone $registrations)->count();
        $paid = (clone $registrations)->whereIn('status', ProgramRegistrationStatus::seatConsumingValues())->count();
        $incompleteProfiles = (clone $registrations)
            ->whereIn('status', ProgramRegistrationStatus::seatConsumingValues())
            ->whereNull('profile_completed_at')
            ->count();

        $seatSummary = $edition->tracks
            ->map(fn ($track) => "{$track->name}: {$track->seatsTaken()}/{$track->capacity}")
            ->implode(' · ');

        return [
            Stat::make($edition->title.' — registrations', number_format($started))
                ->description('Total funnel entries')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary'),

            Stat::make('Seats confirmed', number_format($paid))
                ->description($seatSummary ?: 'No tracks configured')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make('Profiles incomplete', number_format($incompleteProfiles))
                ->description($incompleteProfiles > 0 ? 'Certificates blocked until complete' : 'All paid seats onboarded')
                ->descriptionIcon($incompleteProfiles > 0 ? 'heroicon-m-exclamation-circle' : 'heroicon-m-check-circle')
                ->color($incompleteProfiles > 0 ? 'warning' : 'success'),
        ];
    }
}
