<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

class FutureModule extends Model
{
    protected $fillable = [
        'key',
        'name',
        'summary',
        'status',
        'module_group',
        'public_path',
        'is_publicly_visible',
        'sort_order',
        'readiness_checks',
        'metadata',
    ];

    protected $casts = [
        'is_publicly_visible' => 'boolean',
        'readiness_checks' => 'array',
        'metadata' => 'array',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->is_publicly_visible;
    }
}
