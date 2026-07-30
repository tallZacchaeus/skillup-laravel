<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodleGroup extends Model
{
    protected $fillable = [
        'moodle_connection_id',
        'moodle_group_id',
        'moodle_course_id',
        'name',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(MoodleConnection::class, 'moodle_connection_id');
    }
}
