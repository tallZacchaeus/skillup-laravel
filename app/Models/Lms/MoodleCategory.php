<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodleCategory extends Model
{
    protected $fillable = [
        'moodle_connection_id',
        'moodle_category_id',
        'name',
        'parent_id',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(MoodleConnection::class, 'moodle_connection_id');
    }
}
