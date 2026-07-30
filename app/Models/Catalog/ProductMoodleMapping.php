<?php

namespace App\Models\Catalog;

use Database\Factories\Catalog\ProductMoodleMappingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMoodleMapping extends Model
{
    /** @use HasFactory<ProductMoodleMappingFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'moodle_connection_id',
        'moodle_course_id',
        'moodle_category_id',
        'moodle_group_id',
        'moodle_cohort_id',
        'is_primary',
        'sync_enabled',
        'last_synced_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sync_enabled' => 'boolean',
            'last_synced_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function moodleConnection(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Lms\MoodleConnection::class);
    }
}
