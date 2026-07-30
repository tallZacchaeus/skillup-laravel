<?php

namespace App\Models\Lms;

use App\Models\Catalog\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsSyncLog extends Model
{
    protected $fillable = [
        'enrollment_id',
        'user_id',
        'action',
        'status',
        'error_message',
        'attempts',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
