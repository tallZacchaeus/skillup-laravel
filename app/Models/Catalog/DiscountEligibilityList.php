<?php

namespace App\Models\Catalog;

use App\Models\User;
use Database\Factories\Catalog\DiscountEligibilityListFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountEligibilityList extends Model
{
    /** @use HasFactory<DiscountEligibilityListFactory> */
    use HasFactory;

    protected $fillable = [
        'discount_rule_id',
        'uploaded_by_user_id',
        'name',
        'slug',
        'description',
        'source_filename',
        'total_emails',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function discountRule(): BelongsTo
    {
        return $this->belongsTo(DiscountRule::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(DiscountEligibleEmail::class);
    }

    public function refreshEmailCount(): void
    {
        $this->forceFill(['total_emails' => $this->emails()->count()])->save();
    }
}
