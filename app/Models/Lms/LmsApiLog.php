<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsApiLog extends Model
{
    protected $fillable = [
        'moodle_connection_id',
        'endpoint',
        'request_payload',
        'response_payload',
        'response_status',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(MoodleConnection::class, 'moodle_connection_id');
    }
}
