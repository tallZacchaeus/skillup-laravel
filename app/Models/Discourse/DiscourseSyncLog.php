<?php

namespace App\Models\Discourse;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscourseSyncLog extends Model
{
    protected $fillable = [
        'discourse_connection_id',
        'user_id',
        'action',
        'status',
        'payload',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(DiscourseConnection::class, 'discourse_connection_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
