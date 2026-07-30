<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Downloadable extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_category_id',
        'title',
        'slug',
        'description',
        'file_path',
        'cover_image',
        'download_count',
        'status',
        'is_gated',
    ];

    protected $casts = [
        'is_gated' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ResourceCategory::class, 'resource_category_id');
    }
}
