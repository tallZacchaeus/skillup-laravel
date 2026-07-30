<?php

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTemplate extends Model
{
    protected $fillable = [
        'notification_event_id',
        'name',
        'subject',
        'body_html',
        'body_text',
        'variables',
    ];

    protected $casts = [
        'variables' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(NotificationEvent::class, 'notification_event_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(EmailMessage::class);
    }
}
