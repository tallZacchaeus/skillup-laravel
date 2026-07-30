<?php

namespace App\Models\Discourse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscourseConnection extends Model
{
    protected $fillable = [
        'name',
        'base_url',
        'sso_secret',
        'api_key',
        'api_username',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sso_secret' => 'encrypted',
        'api_key' => 'encrypted',
    ];

    public function groups(): HasMany
    {
        return $this->hasMany(DiscourseGroup::class);
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(DiscourseGroupMapping::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(DiscourseSyncLog::class);
    }
}
