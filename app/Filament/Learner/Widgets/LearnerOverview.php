<?php

namespace App\Filament\Learner\Widgets;

use App\Enums\EnrollmentStatus;
use App\Enums\InstallmentStatus;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Installment;
use App\Models\Support\SupportTicket;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LearnerOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $userId = auth()->id();

        $activeCourses = Enrollment::where('user_id', $userId)
            ->where('status', EnrollmentStatus::Active->value)
            ->count();
        $pendingCourses = Enrollment::where('user_id', $userId)
            ->where('status', EnrollmentStatus::Pending->value)
            ->count();

        $nextInstallment = Installment::whereHas('order', fn ($query) => $query->where('user_id', $userId))
            ->whereIn('status', [InstallmentStatus::Pending->value, InstallmentStatus::Overdue->value])
            ->orderBy('due_at')
            ->first();
        $hasOverdue = $nextInstallment && $nextInstallment->status->value === InstallmentStatus::Overdue->value;

        $openTickets = SupportTicket::where('user_id', $userId)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        return [
            Stat::make('Active courses', number_format($activeCourses))
                ->description($activeCourses > 0 ? 'Keep up the momentum' : 'Enroll to get started')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color($activeCourses > 0 ? 'success' : 'gray'),

            Stat::make('Awaiting activation', number_format($pendingCourses))
                ->description($pendingCourses > 0 ? 'Being set up on Moodle' : 'Nothing pending')
                ->descriptionIcon($pendingCourses > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($pendingCourses > 0 ? 'warning' : 'success'),

            Stat::make(
                'Next payment due',
                $nextInstallment
                    ? $nextInstallment->currency.' '.number_format((float) ($nextInstallment->amount - $nextInstallment->amount_paid), 2)
                    : 'Nothing due',
            )
                ->description($nextInstallment?->due_at ? 'Due '.$nextInstallment->due_at->format('M j, Y') : 'You are all paid up')
                ->descriptionIcon($nextInstallment ? 'heroicon-m-banknotes' : 'heroicon-m-check-circle')
                ->color($hasOverdue ? 'danger' : ($nextInstallment ? 'warning' : 'success')),

            Stat::make('Open support tickets', number_format($openTickets))
                ->description($openTickets > 0 ? 'We are on it' : 'No open requests')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($openTickets > 0 ? 'warning' : 'success'),
        ];
    }
}
