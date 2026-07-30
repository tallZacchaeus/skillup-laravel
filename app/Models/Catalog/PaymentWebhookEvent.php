<?php

namespace App\Models\Catalog;

use App\Enums\WebhookEventStatus;
use Illuminate\Database\Eloquent\Model;

class PaymentWebhookEvent extends Model
{
    protected $fillable = [
        'provider',
        'event',
        'event_key',
        'reference',
        'signature',
        'payload_hash',
        'status',
        'payload',
        'error',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => WebhookEventStatus::class,
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
