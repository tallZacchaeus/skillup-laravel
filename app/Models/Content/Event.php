<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'type',
        'starts_at',
        'ends_at',
        'registration_limit',
        'registered_count',
        'status',
        'recording_url',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(EventRecording::class);
    }

    public function refreshRegisteredCount(): void
    {
        $this->updateQuietly([
            'registered_count' => $this->registrations()->count(),
        ]);
    }
}
