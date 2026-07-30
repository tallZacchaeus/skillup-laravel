<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'email',
        'name',
        'phone',
        'type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
