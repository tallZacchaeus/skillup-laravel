<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Models\Notifications\WhatsappMessage;
use App\Jobs\Notifications\SendWhatsappMessageJob;

class Phase9WhatsappChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toPhase9Whatsapp')) {
            return;
        }

        $whatsappData = $notification->toPhase9Whatsapp($notifiable);
        if (!$whatsappData) {
            return;
        }

        $recipientPhone = $whatsappData['recipient_phone'] 
            ?? $notifiable->routeNotificationFor('whatsapp', $notification) 
            ?? $notifiable->phone 
            ?? $notifiable->learnerProfile?->phone 
            ?? null;
        if (!$recipientPhone) {
            return;
        }

        $templateName = $whatsappData['template_name'] ?? null;
        if (!$templateName) {
            return;
        }

        $whatsappMessage = WhatsappMessage::create([
            'user_id' => $notifiable instanceof \App\Models\User ? $notifiable->id : null,
            'recipient_phone' => $recipientPhone,
            'template_name' => $templateName,
            'status' => 'queued',
        ]);

        SendWhatsappMessageJob::dispatch(
            $whatsappMessage,
            $whatsappData['variables'] ?? [],
            $whatsappData['language_code'] ?? 'en_US'
        );
    }
}
