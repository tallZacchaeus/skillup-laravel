<?php

namespace App\Models\Programs;

use App\Enums\ProgramEditionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'tagline',
        'description',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function editions(): HasMany
    {
        return $this->hasMany(ProgramEdition::class)->orderByDesc('year');
    }

    public function currentEdition(): ?ProgramEdition
    {
        return $this->editions()
            ->whereIn('status', [
                ProgramEditionStatus::RegistrationOpen->value,
                ProgramEditionStatus::SoldOut->value,
                ProgramEditionStatus::Running->value,
                ProgramEditionStatus::Announced->value,
            ])
            ->orderByDesc('year')
            ->first()
            ?? $this->editions()
                ->whereIn('status', ProgramEditionStatus::publicValues())
                ->orderByDesc('year')
                ->first();
    }
}
