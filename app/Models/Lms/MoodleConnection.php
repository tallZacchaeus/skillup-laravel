<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MoodleConnection extends Model
{
    protected $fillable = [
        'name',
        'base_url',
        'token',
        'service_name',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata' => 'array',
            'token' => 'encrypted',
        ];
    }

    public function courses(): HasMany
    {
        return $this->hasMany(MoodleCourse::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(LmsAccount::class);
    }
}
