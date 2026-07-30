<?php

namespace App\Notifications;

use App\Models\Catalog\Enrollment;
use App\Notifications\Channels\Phase9EmailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MoodleAccessFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Enrollment $enrollment,
        public readonly string $errorMsg,
    ) {}

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
        return [
            'subject' => 'LMS Access Sync Failed',
            'message' => 'Provisioning access for ' . ($this->enrollment->product?->title ?? 'Course') . ' failed: ' . $this->errorMsg,
        ];
    }

    public function toPhase9Email(object $notifiable): array
    {
        return [
            'template_name' => 'Moodle Access Failure Alert',
            'variables' => [
                'name' => $this->enrollment->user?->name ?? 'Learner',
                'course_name' => $this->enrollment->product?->title ?? 'Course',
                'error_message' => $this->errorMsg,
            ],
        ];
    }

    public function toPhase9Whatsapp(object $notifiable): array
    {
        return [
            'template_name' => 'moodle_provisioning_failed',
            'variables' => [
                ['type' => 'text', 'text' => $this->enrollment->product?->title ?? 'Course'],
                ['type' => 'text', 'text' => substr($this->errorMsg, 0, 100)],
            ],
        ];
    }
}
