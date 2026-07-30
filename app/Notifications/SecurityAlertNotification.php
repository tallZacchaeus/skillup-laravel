<?php

namespace App\Notifications;

use App\Notifications\Channels\Phase9EmailChannel;
use App\Notifications\Channels\Phase9WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SecurityAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $alertType = 'password_change',
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
        return [
            'subject' => 'Security Alert: Password Changed',
            'message' => 'Hello ' . ($notifiable->name ?? 'Learner') . ', your SkillUp password was recently changed. If this wasn\'t you, please contact support immediately.',
        ];
    }

    public function toPhase9Email(object $notifiable): array
    {
        return [
            'template_name' => 'Password Changed Alert',
            'variables' => [
                'name' => $notifiable->name ?? 'Learner',
            ],
        ];
    }

    public function toPhase9Whatsapp(object $notifiable): array
    {
        return [
            'template_name' => 'security_alert',
            'variables' => [
                ['type' => 'text', 'text' => $notifiable->name ?? 'Learner'],
                ['type' => 'text', 'text' => $this->alertType],
            ],
        ];
    }
}
