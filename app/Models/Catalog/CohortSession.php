<?php

namespace App\Models\Catalog;

use Database\Factories\Catalog\CohortSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CohortSession extends Model
{
    /** @use HasFactory<CohortSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'cohort_id',
        'title',
        'starts_at',
        'ends_at',
        'delivery_mode',
        'meeting_url',
        'location',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }
}
