<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'student_name',
        'avatar_path',
        'course_title',
        'rating',
        'quote',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];
}
