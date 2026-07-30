<?php

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappDeliveryLog extends Model
{
    protected $fillable = [
        'whatsapp_message_id',
        'provider_message_id',
        'status',
        'error_message',
        'attempt_number',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(WhatsappMessage::class, 'whatsapp_message_id');
    }
}
