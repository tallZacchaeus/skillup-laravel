<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackClient
{
    public function initialize(array $payload): Response
    {
        return $this->request()->post('/transaction/initialize', $payload);
    }

    public function verify(string $reference): Response
    {
        return $this->request()->get('/transaction/verify/'.$reference);
    }

    public function createRefund(array $payload): Response
    {
        return $this->request()->post('/refund', $payload);
    }

    private function request(): \Illuminate\Http\Client\PendingRequest
    {
        $secret = config('services.paystack.secret_key');

        if (blank($secret)) {
            throw new RuntimeException('PAYSTACK_SECRET_KEY is not configured.');
        }

        return Http::baseUrl(config('services.paystack.payment_url', 'https://api.paystack.co'))
            ->acceptJson()
            ->asJson()
            ->withToken($secret);
    }
}
