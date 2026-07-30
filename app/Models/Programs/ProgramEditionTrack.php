<?php

namespace App\Models\Programs;

use App\Enums\ProgramRegistrationStatus;
use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramEditionTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_edition_id',
        'product_id',
        'name',
        'slug',
        'age_min',
        'age_max',
        'capacity',
        'summary',
        'curriculum',
        'facilitator_note',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'curriculum' => 'array',
        'metadata' => 'array',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(ProgramEdition::class, 'program_edition_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(ProgramRegistration::class);
    }

    public function acceptsAge(int $age): bool
    {
        return ($this->age_min === null || $age >= $this->age_min)
            && ($this->age_max === null || $age <= $this->age_max);
    }

    /** Confirmed seats plus unexpired checkout holds. */
    public function seatsTaken(): int
    {
        return $this->registrations()
            ->where(function ($query) {
                $query->whereIn('status', ProgramRegistrationStatus::seatConsumingValues())
                    ->orWhere('seat_held_until', '>', now());
            })
            ->count();
    }

    public function seatsRemaining(): ?int
    {
        if ($this->capacity === null) {
            return null;
        }

        return max(0, $this->capacity - $this->seatsTaken());
    }

    public function isFull(): bool
    {
        $remaining = $this->seatsRemaining();

        return $remaining !== null && $remaining <= 0;
    }
}
