<?php

namespace App\Filament\Corporate\Widgets;

use App\Enums\EnrollmentStatus;
use App\Filament\Corporate\Concerns\ScopesCorporateAccount;
use App\Filament\Corporate\Resources\CorporateOrderResource;
use App\Filament\Corporate\Resources\CorporateTeamMemberResource;
use App\Models\Catalog\CorporateAccount;
use App\Models\Catalog\CorporateLearner;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Order;
use App\Models\Support\SupportTicket;
use Filament\Widgets\Widget;

/**
 * Executive welcome banner for the corporate dashboard. Time-aware greeting,
 * organisation name, and high-priority alerts derived entirely from real data
 * (pending invites, outstanding balance, open tickets, pending activations).
 * No fabricated status is shown.
 */
class CorporateHero extends Widget
{
    use ScopesCorporateAccount;

    protected static string $view = 'filament.corporate.widgets.hero';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -3;

    protected function getViewData(): array
    {
        $accountIds = static::corporateAccountIds();
        $account = CorporateAccount::whereIn('id', $accountIds)->first();

        $invited = CorporateLearner::whereIn('corporate_account_id', $accountIds)->where('status', 'invited')->count();
        $pendingEnrollments = Enrollment::whereIn('corporate_account_id', $accountIds)->where('status', EnrollmentStatus::Pending->value)->count();
        $currency = Order::whereIn('corporate_account_id', $accountIds)->value('currency') ?? 'NGN';
        $outstanding = (float) Order::whereIn('corporate_account_id', $accountIds)->sum('balance_due');
        $openTickets = SupportTicket::whereIn('corporate_account_id', $accountIds)->whereNotIn('status', ['resolved', 'closed'])->count();

        $alerts = [];
        if ($outstanding > 0) {
            $alerts[] = ['label' => 'Outstanding balance of '.$currency.' '.number_format($outstanding, 2), 'tone' => 'danger', 'url' => CorporateOrderResource::getUrl()];
        }
        if ($invited > 0) {
            $alerts[] = ['label' => $invited.' '.\Illuminate\Support\Str::plural('invitation', $invited).' still pending', 'tone' => 'warning', 'url' => CorporateTeamMemberResource::getUrl()];
        }
        if ($pendingEnrollments > 0) {
            $alerts[] = ['label' => $pendingEnrollments.' '.\Illuminate\Support\Str::plural('enrollment', $pendingEnrollments).' awaiting activation', 'tone' => 'warning', 'url' => null];
        }
        if ($openTickets > 0) {
            $alerts[] = ['label' => $openTickets.' open support '.\Illuminate\Support\Str::plural('ticket', $openTickets), 'tone' => 'info', 'url' => null];
        }

        return [
            'greeting' => $this->greeting(),
            'contactName' => auth()->user()?->name,
            'orgName' => $account?->name,
            'alerts' => $alerts,
            'inviteUrl' => CorporateTeamMemberResource::getUrl(),
            'billingUrl' => CorporateOrderResource::getUrl(),
        ];
    }

    private function greeting(): string
    {
        $hour = (int) now()->format('G');

        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 18 => 'Good afternoon',
            default => 'Good evening',
        };
    }
}
