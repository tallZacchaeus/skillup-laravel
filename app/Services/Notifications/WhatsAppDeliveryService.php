<?php

namespace App\Services\Notifications;

use App\Models\Notifications\WhatsappMessage;
use App\Models\Notifications\WhatsappDeliveryLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppDeliveryService
{
    public function send(WhatsappMessage $message, array $templateParams = [], string $languageCode = 'en_US'): bool
    {
        $message->update(['status' => 'sending']);

        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $accessToken = config('services.whatsapp.access_token');
        $apiVersion = config('services.whatsapp.api_version');
        $baseUrl = config('services.whatsapp.base_url');

        if (empty($phoneNumberId) || empty($accessToken)) {
            $this->logFailure($message, "WhatsApp configuration is missing.");
            return false;
        }

        $url = "{$baseUrl}/{$apiVersion}/{$phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $message->recipient_phone,
            'type' => 'template',
            'template' => [
                'name' => $message->template_name,
                'language' => [
                    'code' => $languageCode,
                ],
            ],
        ];

        if (!empty($templateParams)) {
            $payload['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => $templateParams,
                ]
            ];
        }

        try {
            $response = Http::withToken($accessToken)
                ->post($url, $payload);

            $attempt = $message->deliveryLogs()->count() + 1;

            if ($response->successful()) {
                $data = $response->json();
                WhatsappDeliveryLog::create([
                    'whatsapp_message_id' => $message->id,
                    'provider_message_id' => $data['messages'][0]['id'] ?? null,
                    'status' => 'success',
                    'attempt_number' => $attempt,
                ]);
                $message->update(['status' => 'sent']);
                return true;
            } else {
                $errorMsg = $response->body();
                WhatsappDeliveryLog::create([
                    'whatsapp_message_id' => $message->id,
                    'status' => 'failed',
                    'error_message' => $errorMsg,
                    'attempt_number' => $attempt,
                ]);
                $message->update(['status' => 'failed']);
                return false;
            }
        } catch (Exception $e) {
            $this->logFailure($message, $e->getMessage());
            return false;
        }
    }

    protected function logFailure(WhatsappMessage $message, string $error): void
    {
        $attempt = $message->deliveryLogs()->count() + 1;
        WhatsappDeliveryLog::create([
            'whatsapp_message_id' => $message->id,
            'status' => 'failed',
            'error_message' => $error,
            'attempt_number' => $attempt,
        ]);
        $message->update(['status' => 'failed']);
        Log::error("WhatsApp failed for message {$message->id}: {$error}");
    }
}
