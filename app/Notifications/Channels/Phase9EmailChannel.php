<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Models\Notifications\EmailMessage;
use App\Models\Notifications\EmailTemplate;
use App\Jobs\Notifications\SendEmailMessageJob;

class Phase9EmailChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toPhase9Email')) {
            return;
        }

        $emailData = $notification->toPhase9Email($notifiable);
        if (!$emailData) {
            return;
        }

        $recipientEmail = $emailData['recipient_email'] ?? $notifiable->routeNotificationFor('mail', $notification) ?? $notifiable->email ?? null;
        if (!$recipientEmail) {
            return;
        }

        $templateName = $emailData['template_name'] ?? null;
        $templateId = null;
        $subject = $emailData['subject'] ?? '';
        $bodyHtml = $emailData['body_html'] ?? '';

        if ($templateName) {
            $template = EmailTemplate::where('name', $templateName)->first();
            if ($template) {
                $templateId = $template->id;
                $subject = $template->subject;
                $bodyHtml = $template->body_html;

                $variables = $emailData['variables'] ?? [];
                foreach ($variables as $key => $val) {
                    $subject = str_replace(['{{' . $key . '}}', '{{ ' . $key . ' }}'], (string)$val, $subject);
                    $bodyHtml = str_replace(['{{' . $key . '}}', '{{ ' . $key . ' }}'], (string)$val, $bodyHtml);
                }
            }
        }

        $emailMessage = EmailMessage::create([
            'user_id' => $notifiable instanceof \App\Models\User ? $notifiable->id : null,
            'email_template_id' => $templateId,
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'status' => 'queued',
        ]);

        SendEmailMessageJob::dispatch($emailMessage);
    }
}
