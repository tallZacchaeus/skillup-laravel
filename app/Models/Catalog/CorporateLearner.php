<?php

namespace App\Models\Catalog;

use App\Models\User;
use Database\Factories\Catalog\CorporateLearnerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorporateLearner extends Model
{
    /** @use HasFactory<CorporateLearnerFactory> */
    use HasFactory;

    protected $fillable = [
        'corporate_account_id',
        'user_id',
        'name',
        'email',
        'status',
        'invited_at',
        'accepted_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'accepted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function corporateAccount(): BelongsTo
    {
        return $this->belongsTo(CorporateAccount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
