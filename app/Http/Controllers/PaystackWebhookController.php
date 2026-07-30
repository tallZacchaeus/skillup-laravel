<?php

namespace App\Http\Controllers;

use App\Enums\WebhookEventStatus;
use App\Models\Catalog\PaymentWebhookEvent;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaystackWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentService $payments): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signature = (string) $request->header('x-paystack-signature');
        $secret = (string) config('services.paystack.webhook_secret');

        // Hard-fail when the secret is missing: never accept unsigned/forgeable
        // payment events. (Previously an empty secret silently skipped verification.)
        if ($secret === '') {
            Log::error('Paystack webhook rejected: PAYSTACK_WEBHOOK_SECRET is not configured.');

            return response()->json(['message' => 'Webhook secret not configured'], 500);
        }

        if (! hash_equals(hash_hmac('sha512', $rawPayload, $secret), $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = $request->json()->all();
        $event = (string) data_get($payload, 'event');
        $reference = (string) data_get($payload, 'data.reference');
        $eventKey = $event.':'.($reference ?: data_get($payload, 'data.id', sha1($rawPayload)));
        $payloadHash = hash('sha256', $rawPayload);

        $existing = PaymentWebhookEvent::query()
            ->where('payload_hash', $payloadHash)
            ->orWhere(fn ($query) => $query->where('provider', 'paystack')->where('event_key', $eventKey))
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Duplicate webhook ignored']);
        }

        $webhook = PaymentWebhookEvent::create([
            'provider' => 'paystack',
            'event' => $event,
            'event_key' => $eventKey,
            'reference' => $reference ?: null,
            'signature' => $signature ?: null,
            'payload_hash' => $payloadHash,
            'payload' => $payload,
        ]);

        $refundEvents = ['refund.processed', 'refund.failed', 'refund.pending'];

        try {
            $handled = false;

            if ($event === 'charge.success' && $reference !== '') {
                $payments->verifyPaystackReference($reference);
                $handled = true;
            } elseif (in_array($event, $refundEvents, true)) {
                $payments->handleRefundEvent($event, (array) data_get($payload, 'data', []));
                $handled = true;
            }

            $webhook->update([
                'status' => $handled ? WebhookEventStatus::Processed : WebhookEventStatus::Ignored,
                'processed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $webhook->update([
                'status' => WebhookEventStatus::Failed,
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json(['message' => 'Webhook accepted']);
    }
}
