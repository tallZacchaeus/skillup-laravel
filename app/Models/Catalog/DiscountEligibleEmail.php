<?php

namespace App\Models\Catalog;

use Database\Factories\Catalog\DiscountEligibleEmailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DiscountEligibleEmail extends Model
{
    /** @use HasFactory<DiscountEligibleEmailFactory> */
    use HasFactory;

    protected $fillable = [
        'discount_eligibility_list_id',
        'email',
        'normalized_email',
        'name',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (DiscountEligibleEmail $email) {
            $email->normalized_email = static::normalizeEmail($email->email);
            $email->email = $email->normalized_email;
        });
    }

    public static function normalizeEmail(?string $email): string
    {
        return Str::lower(trim((string) $email));
    }

    public function eligibilityList(): BelongsTo
    {
        return $this->belongsTo(DiscountEligibilityList::class, 'discount_eligibility_list_id');
    }
}
