<?php

namespace App\Filament\Widgets;

use App\Enums\EnrollmentStatus;
use App\Enums\InstallmentStatus;
use App\Enums\WebhookEventStatus;
use App\Filament\Resources\EnrollmentResource;
use App\Filament\Resources\InstallmentResource;
use App\Filament\Resources\PaymentWebhookEventResource;
use App\Filament\Resources\SupportTicketResource;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Installment;
use App\Models\Catalog\PaymentWebhookEvent;
use App\Models\Support\SupportTicket;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsAttention extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '60s';

    protected ?string $heading = 'Needs attention';

    protected function getStats(): array
    {
        $failedWebhooks = PaymentWebhookEvent::where('status', WebhookEventStatus::Failed->value)->count();
        $brokenEnrollments = Enrollment::whereIn('status', [
            EnrollmentStatus::Failed->value,
            EnrollmentStatus::Partial->value,
        ])->count();
        $overdueInstallments = Installment::where('status', InstallmentStatus::Overdue->value)->count();
        $openTickets = SupportTicket::whereNotIn('status', ['resolved', 'closed'])->count();

        return [
            Stat::make('Failed payment webhooks', number_format($failedWebhooks))
                ->description($failedWebhooks > 0 ? 'Review and replay' : 'All processed')
                ->descriptionIcon($failedWebhooks > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($failedWebhooks > 0 ? 'danger' : 'success')
                ->url(PaymentWebhookEventResource::getUrl('index')),

            Stat::make('Broken Moodle enrollments', number_format($brokenEnrollments))
                ->description($brokenEnrollments > 0 ? 'Failed or partial — retry needed' : 'All provisioned')
                ->descriptionIcon($brokenEnrollments > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($brokenEnrollments > 0 ? 'danger' : 'success')
                ->url(EnrollmentResource::getUrl('index')),

            Stat::make('Overdue installments', number_format($overdueInstallments))
                ->description($overdueInstallments > 0 ? 'Follow up on payment plans' : 'Nothing overdue')
                ->descriptionIcon($overdueInstallments > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($overdueInstallments > 0 ? 'warning' : 'success')
                ->url(InstallmentResource::getUrl('index')),

            Stat::make('Open support tickets', number_format($openTickets))
                ->description($openTickets > 0 ? 'Awaiting response' : 'Inbox clear')
                ->descriptionIcon($openTickets > 0 ? 'heroicon-m-chat-bubble-left-right' : 'heroicon-m-check-circle')
                ->color($openTickets > 0 ? 'warning' : 'success')
                ->url(SupportTicketResource::getUrl('index')),
        ];
    }
}
