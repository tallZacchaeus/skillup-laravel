<?php

namespace App\Notifications;

use App\Models\Catalog\Payment;
use App\Notifications\Channels\Phase9EmailChannel;
use App\Notifications\Channels\Phase9WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Payment $payment,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        if ($notifiable instanceof \App\Models\User) {
            $channels[] = 'database';
        }
        $channels[] = Phase9EmailChannel::class;
        $channels[] = Phase9WhatsappChannel::class;
        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        $amount = $this->payment->currency . ' ' . number_format((float) $this->payment->amount, 2);

        return [
            'subject' => 'Failed Payment Attempt',
            'message' => 'Your payment attempt of ' . $amount . ' for order ' . ($this->payment->order?->order_number ?? '') . ' has failed.',
        ];
    }

    public function toPhase9Email(object $notifiable): array
    {
        $amount = $this->payment->currency . ' ' . number_format((float) $this->payment->amount, 2);

        return [
            'template_name' => 'Payment Failed Alert',
            'variables' => [
                'name' => $this->payment->user?->name ?? 'Learner',
                'amount' => $amount,
                'order_number' => $this->payment->order?->order_number ?? '',
            ],
        ];
    }

    public function toPhase9Whatsapp(object $notifiable): array
    {
        $amount = $this->payment->currency . ' ' . number_format((float) $this->payment->amount, 2);

        return [
            'template_name' => 'payment_failed',
            'variables' => [
                ['type' => 'text', 'text' => $this->payment->order?->order_number ?? ''],
                ['type' => 'text', 'text' => $amount],
            ],
        ];
    }
}
