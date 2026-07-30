<?php

namespace App\Models\Content;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'name',
        'email',
        'phone',
    ];

    protected static function booted(): void
    {
        static::created(function (EventRegistration $registration): void {
            $registration->event?->refreshRegisteredCount();
        });

        static::deleted(function (EventRegistration $registration): void {
            $registration->event?->refreshRegisteredCount();
        });

        static::updated(function (EventRegistration $registration): void {
            if (! $registration->wasChanged('event_id')) {
                return;
            }

            Event::find($registration->getOriginal('event_id'))?->refreshRegisteredCount();
            $registration->event?->refreshRegisteredCount();
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
