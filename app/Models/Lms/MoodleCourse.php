<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodleCourse extends Model
{
    protected $fillable = [
        'moodle_connection_id',
        'moodle_course_id',
        'shortname',
        'fullname',
        'summary',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(MoodleConnection::class, 'moodle_connection_id');
    }
}
