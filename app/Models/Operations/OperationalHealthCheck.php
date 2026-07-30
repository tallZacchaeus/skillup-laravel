<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;

class OperationalHealthCheck extends Model
{
    protected $fillable = [
        'name',
        'status',
        'checked_at',
        'summary',
        'metrics',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'metrics' => 'array',
    ];
}
