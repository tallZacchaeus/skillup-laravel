<?php

namespace App\Models\Support;

use App\Models\Catalog\CorporateAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'corporate_account_id',
        'assigned_to_id',
        'requester_name',
        'requester_email',
        'subject',
        'category',
        'priority',
        'status',
        'source',
        'last_activity_at',
        'resolved_at',
        'metadata',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'resolved_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupportTicket $ticket): void {
            $ticket->uuid ??= (string) Str::uuid();
            $ticket->last_activity_at ??= now();
        });

        static::saving(function (SupportTicket $ticket): void {
            if ($ticket->isDirty('status') && in_array($ticket->status, ['resolved', 'closed'], true)) {
                $ticket->resolved_at ??= now();
            }

            if ($ticket->isDirty('status') && ! in_array($ticket->status, ['resolved', 'closed'], true)) {
                $ticket->resolved_at = null;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function corporateAccount(): BelongsTo
    {
        return $this->belongsTo(CorporateAccount::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class);
    }
}
