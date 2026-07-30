<?php

namespace App\Notifications;

use App\Notifications\Channels\Phase9EmailChannel;
use App\Notifications\Channels\Phase9WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OtpAuthNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $code,
    ) {}

    public function via(object $notifiable): array
    {
        return [
            Phase9EmailChannel::class,
            Phase9WhatsappChannel::class,
        ];
    }

    public function toPhase9Email(object $notifiable): array
    {
        return [
            'template_name' => 'OTP Code Verification',
            'variables' => [
                'code' => $this->code,
            ],
        ];
    }

    public function toPhase9Whatsapp(object $notifiable): array
    {
        return [
            'template_name' => 'otp_auth',
            'variables' => [
                ['type' => 'text', 'text' => $this->code],
            ],
        ];
    }
}
