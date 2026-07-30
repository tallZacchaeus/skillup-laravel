<?php

namespace App\Http\Controllers;

use App\Models\Programs\ProgramRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives Resend delivery events (Svix-signed). Hard bounces and complaints
 * mark the matching program registration's email invalid so nudges fall back
 * to WhatsApp and admins see the badge.
 */
class ResendWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->verifySignature($request)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $type = (string) $request->input('type');
        $email = strtolower((string) data_get($request->input('data'), 'to.0', ''));

        if ($email === '') {
            return response()->json(['message' => 'Ignored.']);
        }

        if (in_array($type, ['email.bounced', 'email.complained'], true)) {
            $updated = ProgramRegistration::query()
                ->where('guardian_email', $email)
                ->whereNull('email_invalid_at')
                ->update(['email_invalid_at' => now()]);

            Log::info("Resend webhook {$type} for {$email}: flagged {$updated} registration(s).");
        }

        return response()->json(['message' => 'ok']);
    }

    private function verifySignature(Request $request): bool
    {
        $secret = config('services.resend.webhook_secret');

        if (blank($secret)) {
            // No secret configured (local/dev) — accept but log, so staging works pre-config.
            Log::warning('Resend webhook received without a configured RESEND_WEBHOOK_SECRET.');

            return true;
        }

        $svixId = $request->header('svix-id');
        $svixTimestamp = $request->header('svix-timestamp');
        $svixSignature = $request->header('svix-signature');

        if (! $svixId || ! $svixTimestamp || ! $svixSignature) {
            return false;
        }

        if (abs(now()->timestamp - (int) $svixTimestamp) > 300) {
            return false;
        }

        $secretKey = base64_decode(str_replace('whsec_', '', $secret));
        $signedContent = "{$svixId}.{$svixTimestamp}.".$request->getContent();
        $expected = base64_encode(hash_hmac('sha256', $signedContent, $secretKey, true));

        foreach (explode(' ', $svixSignature) as $candidate) {
            $parts = explode(',', $candidate, 2);

            if (count($parts) === 2 && hash_equals($expected, $parts[1])) {
                return true;
            }
        }

        return false;
    }
}
