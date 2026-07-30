<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsAccount extends Model
{
    protected $fillable = [
        'user_id',
        'moodle_connection_id',
        'moodle_user_id',
        'moodle_username',
        'sync_status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(MoodleConnection::class, 'moodle_connection_id');
    }
}
