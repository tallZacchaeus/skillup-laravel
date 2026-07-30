<?php

namespace App\Notifications;

use App\Models\Catalog\Enrollment;
use App\Notifications\Channels\Phase9EmailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MoodleAccessSuccessNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Enrollment $enrollment,
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
        return [
            'subject' => 'Course Access Active',
            'message' => 'You have successfully been enrolled in ' . ($this->enrollment->product?->title ?? 'Course') . ' on the LMS.',
        ];
    }

    public function toPhase9Email(object $notifiable): array
    {
        return [
            'template_name' => 'Moodle Access Active',
            'variables' => [
                'name' => $this->enrollment->user?->name ?? 'Learner',
                'course_name' => $this->enrollment->product?->title ?? 'Course',
            ],
        ];
    }
}
