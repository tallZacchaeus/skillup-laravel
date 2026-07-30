<?php

namespace App\Notifications;

use App\Models\Content\Event;
use App\Models\Content\EventRegistration;
use App\Notifications\Channels\Phase9EmailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventRegisteredNotification extends Notification
{
    use Queueable;

    public Event $event;
    public EventRegistration $registration;

    public function __construct(Event $event, EventRegistration $registration)
    {
        $this->event = $event;
        $this->registration = $registration;
    }

    public function via(object $notifiable): array
    {
        return [Phase9EmailChannel::class];
    }

    public function toPhase9Email(object $notifiable): array
    {
        $startsAt = $this->event->starts_at->format('M d, Y h:i A');
        $endsAt = $this->event->ends_at->format('h:i A');

        return [
            'template_name' => 'Event Registration Confirmation',
            'subject' => 'Registration Confirmed: ' . $this->event->title,
            'body_html' => '<p>Hello ' . e($this->registration->name) . ',</p>'
                . '<p>Your registration for <strong>' . e($this->event->title) . '</strong> has been confirmed.</p>'
                . '<p><strong>Date and time:</strong> ' . e($startsAt . ' - ' . $endsAt) . '</p>'
                . '<p>We look forward to seeing you there.</p>',
            'variables' => [
                'registrant_name' => e($this->registration->name),
                'event_title' => e($this->event->title),
                'event_type' => e(ucfirst(str_replace('_', ' ', $this->event->type))),
                'starts_at' => e($startsAt),
                'ends_at' => e($endsAt),
            ],
        ];
    }
}
