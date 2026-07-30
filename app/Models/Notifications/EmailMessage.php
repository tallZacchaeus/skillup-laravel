<?php

namespace App\Models\Notifications;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailMessage extends Model
{
    protected $fillable = [
        'user_id',
        'email_template_id',
        'recipient_email',
        'subject',
        'body_html',
        'status', // pending, queued, sending, sent, failed, fallback_sent, cancelled
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function deliveryLogs(): HasMany
    {
        return $this->hasMany(EmailDeliveryLog::class);
    }
}
