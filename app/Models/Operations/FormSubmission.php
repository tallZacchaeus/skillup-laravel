<?php

namespace App\Models\Operations;

use App\Models\Content\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'lead_id',
        'form_key',
        'source_url',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'payload',
        'status',
        'reviewed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
