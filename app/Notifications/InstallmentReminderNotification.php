<?php

namespace App\Notifications;

use App\Models\Catalog\Installment;
use App\Notifications\Channels\Phase9EmailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InstallmentReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Installment $installment,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        if ($notifiable instanceof \App\Models\User) {
            $channels[] = 'database';
        }
        $channels[] = Phase9EmailChannel::class;
        $channels[] = \App\Notifications\Channels\Phase9WhatsappChannel::class;
        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        $installment = $this->installment->loadMissing(['order', 'paymentPlan']);
        $amount = $installment->currency . ' ' . number_format((float) $installment->amount, 2);
        
        return [
            'subject' => 'Installment Payment Reminder',
            'message' => 'An installment of ' . $amount . ' is due for order ' . $installment->order?->order_number . ' on ' . ($installment->due_at?->toFormattedDateString() ?? 'Pending') . '.',
        ];
    }

    public function toPhase9Email(object $notifiable): array
    {
        $installment = $this->installment->loadMissing(['order', 'paymentPlan']);
        $amount = $installment->currency . ' ' . number_format((float) $installment->amount, 2);

        return [
            'template_name' => 'Installment Due Reminder',
            'variables' => [
                'amount' => $amount,
                'due_date' => $installment->due_at?->toFormattedDateString() ?? 'Pending',
                'order_number' => $installment->order?->order_number ?? '',
            ],
        ];
    }

    public function toPhase9Whatsapp(object $notifiable): array
    {
        $installment = $this->installment->loadMissing(['order', 'paymentPlan']);
        $amount = $installment->currency . ' ' . number_format((float) $installment->amount, 2);

        return [
            'template_name' => 'installment_reminder',
            'variables' => [
                ['type' => 'text', 'text' => $amount],
                ['type' => 'text', 'text' => $installment->due_at?->toFormattedDateString() ?? 'Pending'],
            ],
        ];
    }
}
