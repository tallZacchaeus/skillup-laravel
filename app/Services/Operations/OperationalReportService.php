<?php

namespace App\Services\Operations;

use App\Enums\DiscountRedemptionStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Catalog\Cohort;
use App\Models\Catalog\DiscountRedemption;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Order;
use App\Models\Catalog\OrderItem;
use App\Models\Catalog\Payment;
use App\Models\Discourse\DiscourseGroupMapping;
use App\Models\Discourse\DiscourseSyncLog;
use App\Models\Lms\LmsSyncLog;
use App\Models\Notifications\EmailMessage;
use App\Models\Notifications\WhatsappMessage;
use App\Models\Support\SupportTicket;

class OperationalReportService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            'revenue' => $this->revenue(),
            'enrollments' => $this->enrollments(),
            'product_demand' => $this->productDemand(),
            'payments' => $this->payments(),
            'discounts' => $this->discounts(),
            'cohorts' => $this->cohorts(),
            'support' => $this->support(),
            'email' => $this->email(),
            'whatsapp' => $this->whatsapp(),
            'community' => $this->community(),
            'failed_operations' => $this->failedOperations(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function revenue(): array
    {
        return [
            'paid_orders' => Order::where('status', OrderStatus::Paid->value)->count(),
            'gross_revenue' => (float) Order::whereIn('status', [OrderStatus::Paid->value, OrderStatus::PartiallyPaid->value])->sum('amount_paid'),
            'outstanding_balance' => (float) Order::whereIn('payment_status', [PaymentStatus::Pending->value, PaymentStatus::PartiallyPaid->value])->sum('balance_due'),
            'month_to_date' => (float) Order::where('paid_at', '>=', now()->startOfMonth())->sum('amount_paid'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function enrollments(): array
    {
        return [
            'total' => Enrollment::count(),
            'active' => Enrollment::where('status', EnrollmentStatus::Active->value)->count(),
            'pending' => Enrollment::where('status', EnrollmentStatus::Pending->value)->count(),
            'failed' => Enrollment::whereIn('status', [EnrollmentStatus::Failed->value, EnrollmentStatus::Partial->value])->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function productDemand(): array
    {
        return OrderItem::query()
            ->selectRaw('product_title, product_id, SUM(quantity) as quantity, SUM(total) as revenue')
            ->groupBy('product_title', 'product_id')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get()
            ->map(fn (OrderItem $item) => [
                'product_id' => $item->product_id,
                'product_title' => $item->product_title,
                'quantity' => (int) $item->quantity,
                'revenue' => (float) $item->revenue,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function payments(): array
    {
        return [
            'total' => Payment::count(),
            'paid' => Payment::where('status', PaymentStatus::Paid->value)->count(),
            'failed' => Payment::where('status', PaymentStatus::Failed->value)->count(),
            'paid_amount' => (float) Payment::where('status', PaymentStatus::Paid->value)->sum('amount'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function discounts(): array
    {
        return [
            'redemptions' => DiscountRedemption::where('status', DiscountRedemptionStatus::Redeemed->value)->count(),
            'locked' => DiscountRedemption::where('status', DiscountRedemptionStatus::Locked->value)->count(),
            'discount_amount' => (float) DiscountRedemption::where('status', DiscountRedemptionStatus::Redeemed->value)->sum('discount_amount'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function cohorts(): array
    {
        return [
            'total' => Cohort::count(),
            'open' => Cohort::where('status', 'open')->count(),
            'in_progress' => Cohort::where('status', 'in_progress')->count(),
            'completed' => Cohort::where('status', 'completed')->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function support(): array
    {
        return [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::whereIn('status', ['open', 'pending'])->count(),
            'urgent' => SupportTicket::whereIn('priority', ['high', 'urgent'])->whereNotIn('status', ['resolved', 'closed'])->count(),
            'resolved' => SupportTicket::whereIn('status', ['resolved', 'closed'])->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function email(): array
    {
        return [
            'total' => EmailMessage::count(),
            'queued' => EmailMessage::where('status', 'queued')->count(),
            'sent' => EmailMessage::whereIn('status', ['sent', 'fallback_sent'])->count(),
            'failed' => EmailMessage::where('status', 'failed')->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function whatsapp(): array
    {
        return [
            'total' => WhatsappMessage::count(),
            'queued' => WhatsappMessage::where('status', 'queued')->count(),
            'sent' => WhatsappMessage::where('status', 'sent')->count(),
            'failed' => WhatsappMessage::where('status', 'failed')->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function community(): array
    {
        return [
            'sync_logs' => DiscourseSyncLog::count(),
            'failed_syncs' => DiscourseSyncLog::where('status', 'failed')->count(),
            'group_mappings' => DiscourseGroupMapping::count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function failedOperations(): array
    {
        return [
            'moodle_sync_failures' => LmsSyncLog::where('status', 'failed')->count(),
            'payment_failures' => Payment::where('status', PaymentStatus::Failed->value)->count(),
            'email_failures' => EmailMessage::where('status', 'failed')->count(),
            'whatsapp_failures' => WhatsappMessage::where('status', 'failed')->count(),
            'community_sync_failures' => DiscourseSyncLog::where('status', 'failed')->count(),
        ];
    }
}
