<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRecording extends Model
{
    protected $fillable = [
        'event_id',
        'media_asset_id',
        'title',
        'url',
        'description',
        'published_at',
        'is_public',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_public' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }
}
