<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaAsset extends Model
{
    protected $fillable = [
        'title',
        'file_path',
        'disk',
        'collection',
        'alt_text',
        'caption',
        'mime_type',
        'size',
        'metadata',
        'is_public',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
    ];

    public function recordings(): HasMany
    {
        return $this->hasMany(EventRecording::class);
    }
}
