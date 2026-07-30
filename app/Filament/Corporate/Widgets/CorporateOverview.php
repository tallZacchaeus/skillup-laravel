<?php

namespace App\Filament\Corporate\Widgets;

use App\Enums\EnrollmentStatus;
use App\Filament\Corporate\Concerns\ScopesCorporateAccount;
use App\Models\Catalog\CorporateLearner;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Order;
use App\Models\Support\SupportTicket;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CorporateOverview extends StatsOverviewWidget
{
    use ScopesCorporateAccount;

    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $accountIds = static::corporateAccountIds();

        $activeMembers = CorporateLearner::whereIn('corporate_account_id', $accountIds)
            ->where('status', 'active')
            ->count();
        $invitedMembers = CorporateLearner::whereIn('corporate_account_id', $accountIds)
            ->where('status', 'invited')
            ->count();

        $activeEnrollments = Enrollment::whereIn('corporate_account_id', $accountIds)
            ->where('status', EnrollmentStatus::Active->value)
            ->count();
        $pendingEnrollments = Enrollment::whereIn('corporate_account_id', $accountIds)
            ->where('status', EnrollmentStatus::Pending->value)
            ->count();

        $currency = Order::whereIn('corporate_account_id', $accountIds)->value('currency') ?? 'NGN';
        $outstanding = (float) Order::whereIn('corporate_account_id', $accountIds)->sum('balance_due');

        $openTickets = SupportTicket::whereIn('corporate_account_id', $accountIds)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        return [
            Stat::make('Team members', number_format($activeMembers))
                ->description(number_format($invitedMembers).' invites pending')
                ->descriptionIcon($invitedMembers > 0 ? 'heroicon-m-envelope' : 'heroicon-m-check-circle')
                ->color($invitedMembers > 0 ? 'warning' : 'success'),

            Stat::make('Active enrollments', number_format($activeEnrollments))
                ->description(number_format($pendingEnrollments).' pending activation')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color($pendingEnrollments > 0 ? 'warning' : 'success'),

            Stat::make('Outstanding balance', $currency.' '.number_format($outstanding, 2))
                ->description($outstanding > 0 ? 'Across open orders' : 'Fully settled')
                ->descriptionIcon($outstanding > 0 ? 'heroicon-m-banknotes' : 'heroicon-m-check-circle')
                ->color($outstanding > 0 ? 'warning' : 'success'),

            Stat::make('Open support tickets', number_format($openTickets))
                ->description($openTickets > 0 ? 'We are on it' : 'No open requests')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($openTickets > 0 ? 'warning' : 'success'),
        ];
    }
}
