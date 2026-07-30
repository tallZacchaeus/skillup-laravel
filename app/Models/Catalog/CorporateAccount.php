<?php

namespace App\Models\Catalog;

use App\Models\User;
use Database\Factories\Catalog\CorporateAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorporateAccount extends Model
{
    /** @use HasFactory<CorporateAccountFactory> */
    use HasFactory;

    protected $fillable = [
        'primary_contact_user_id',
        'name',
        'slug',
        'contact_name',
        'contact_email',
        'contact_phone',
        'billing_email',
        'billing_address',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function primaryContact(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_contact_user_id');
    }

    public function learners(): HasMany
    {
        return $this->hasMany(CorporateLearner::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
