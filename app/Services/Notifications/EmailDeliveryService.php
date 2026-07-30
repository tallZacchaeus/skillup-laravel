<?php

namespace App\Services\Notifications;

use App\Models\Notifications\EmailMessage;
use App\Models\Notifications\EmailDeliveryLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class EmailDeliveryService
{
    public function send(EmailMessage $message): bool
    {
        $message->update(['status' => 'sending']);

        // Primary: Resend (platform-wide provider).
        try {
            $this->sendViaResend($message);
            $message->update(['status' => 'sent']);
            return true;
        } catch (Exception $resendException) {
            Log::warning("Resend failed for message {$message->id}: " . $resendException->getMessage());
        }

        // Fallback 1: ZeptoMail (legacy primary, kept until retired).
        try {
            $this->sendViaZeptoMail($message);
            $message->update(['status' => 'fallback_sent']);
            return true;
        } catch (Exception $e) {
            Log::warning("ZeptoMail failed for message {$message->id}: " . $e->getMessage());
        }

        // Fallback 2: SES.
        try {
            $this->sendViaSesFallback($message);
            $message->update(['status' => 'fallback_sent']);
            return true;
        } catch (Exception $sesException) {
            Log::error("SES Fallback failed for message {$message->id}: " . $sesException->getMessage());
            $message->update(['status' => 'failed']);
            return false;
        }
    }

    protected function sendViaResend(EmailMessage $message): void
    {
        $apiKey = config('services.resend.api_key');
        $fromAddress = config('services.resend.from_address');
        $fromName = config('services.resend.from_name');

        if (empty($apiKey) || empty($fromAddress)) {
            throw new Exception('Resend configuration is missing.');
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->post(config('services.resend.base_url', 'https://api.resend.com').'/emails', [
                'from' => $fromName ? "{$fromName} <{$fromAddress}>" : $fromAddress,
                'to' => [$message->recipient_email],
                'subject' => $message->subject,
                'html' => $message->body_html,
            ]);

        $attempt = $message->deliveryLogs()->count() + 1;

        if ($response->successful()) {
            EmailDeliveryLog::create([
                'email_message_id' => $message->id,
                'provider' => 'resend',
                'provider_message_id' => $response->json('id'),
                'status' => 'success',
                'attempt_number' => $attempt,
            ]);
        } else {
            $errorMsg = $response->body();
            EmailDeliveryLog::create([
                'email_message_id' => $message->id,
                'provider' => 'resend',
                'status' => 'failed',
                'error_message' => $errorMsg,
                'attempt_number' => $attempt,
            ]);
            throw new Exception("Resend API Error: {$errorMsg}");
        }
    }

    protected function sendViaZeptoMail(EmailMessage $message): void
    {
        $apiKey = config('services.zeptomail.api_key');
        $baseUrl = config('services.zeptomail.base_url');
        $fromAddress = config('services.zeptomail.from_address');
        $fromName = config('services.zeptomail.from_name');

        if (empty($apiKey) || empty($fromAddress)) {
            throw new Exception("ZeptoMail configuration is missing.");
        }

        $payload = [
            'from' => [
                'address' => $fromAddress,
                'name' => $fromName,
            ],
            'to' => [
                [
                    'email_address' => [
                        'address' => $message->recipient_email,
                        'name' => $message->user ? $message->user->name : 'Recipient',
                    ]
                ]
            ],
            'subject' => $message->subject,
            'htmlbody' => $message->body_html,
        ];

        $response = Http::withHeaders([
            'Authorization' => $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($baseUrl . '/email', $payload);

        $attempt = $message->deliveryLogs()->count() + 1;

        if ($response->successful()) {
            $data = $response->json();
            EmailDeliveryLog::create([
                'email_message_id' => $message->id,
                'provider' => 'zeptomail',
                'provider_message_id' => $data['data'][0]['message_id'] ?? null,
                'status' => 'success',
                'attempt_number' => $attempt,
            ]);
        } else {
            $errorMsg = $response->body();
            EmailDeliveryLog::create([
                'email_message_id' => $message->id,
                'provider' => 'zeptomail',
                'status' => 'failed',
                'error_message' => $errorMsg,
                'attempt_number' => $attempt,
            ]);
            throw new Exception("ZeptoMail API Error: {$errorMsg}");
        }
    }

    protected function sendViaSesFallback(EmailMessage $message): void
    {
        $attempt = $message->deliveryLogs()->count() + 1;

        try {
            Mail::mailer('ses')->html($message->body_html, function ($mail) use ($message) {
                $mail->to($message->recipient_email)
                     ->subject($message->subject);
                
                $fromAddress = env('SES_FROM_ADDRESS', config('mail.from.address'));
                $fromName = env('SES_FROM_NAME', config('mail.from.name'));
                
                $mail->from($fromAddress, $fromName);
            });

            EmailDeliveryLog::create([
                'email_message_id' => $message->id,
                'provider' => 'ses',
                'status' => 'success',
                'attempt_number' => $attempt,
            ]);
        } catch (Exception $e) {
            EmailDeliveryLog::create([
                'email_message_id' => $message->id,
                'provider' => 'ses',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'attempt_number' => $attempt,
            ]);
            throw $e;
        }
    }
}
