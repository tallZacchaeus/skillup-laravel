<?php

namespace App\Models\Catalog;

use App\Models\User;
use Database\Factories\Catalog\LearnerProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearnerProfile extends Model
{
    /** @use HasFactory<LearnerProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'country',
        'city',
        'headline',
        'goals',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'goals' => 'array',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
