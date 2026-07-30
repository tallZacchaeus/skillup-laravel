<?php

namespace App\Notifications;

use App\Models\Catalog\Order;
use App\Notifications\Channels\Phase9EmailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderPaidNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        if ($notifiable instanceof \App\Models\User) {
            $channels[] = 'database';
        }
        $channels[] = Phase9EmailChannel::class;
        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        $amount = $this->order->currency . ' ' . number_format((float) $this->order->amount_paid, 2);

        return [
            'subject' => 'Payment Success Receipt',
            'message' => 'Thank you for your payment of ' . $amount . ' for order ' . $this->order->order_number . '.',
        ];
    }

    public function toPhase9Email(object $notifiable): array
    {
        $amount = $this->order->currency . ' ' . number_format((float) $this->order->amount_paid, 2);

        return [
            'template_name' => 'Payment Success Receipt',
            'variables' => [
                'name' => $this->order->user?->name ?? 'Learner',
                'amount' => $amount,
                'order_number' => $this->order->order_number,
            ],
        ];
    }
}
