<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'category',
        'question',
        'answer',
        'sort_order',
    ];
}
