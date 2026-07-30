<?php

namespace App\Models\Programs;

use App\Enums\ProgramEditionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramEdition extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'year',
        'slug',
        'title',
        'theme',
        'status',
        'starts_on',
        'ends_on',
        'schedule_text',
        'delivery_mode',
        'venue_name',
        'venue_address',
        'venue_map_url',
        'capacity_total',
        'payment_mode',
        'age_reference_date',
        'seat_hold_minutes',
        'safeguarding_retention_months',
        'allow_installments',
        'terms_url',
        'refund_policy',
        'content',
        'registration_fields',
        'contact_whatsapp',
        'contact_email',
        'hero_image_path',
        'seo_title',
        'seo_description',
        'metadata',
    ];

    protected $casts = [
        'status' => ProgramEditionStatus::class,
        'starts_on' => 'date',
        'ends_on' => 'date',
        'age_reference_date' => 'date',
        'allow_installments' => 'boolean',
        'content' => 'array',
        'registration_fields' => 'array',
        'metadata' => 'array',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(ProgramEditionTrack::class)->orderBy('sort_order');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(ProgramRegistration::class);
    }

    /** The date ages are computed against (defaults to the program start date). */
    public function ageReferenceDate(): \Illuminate\Support\Carbon
    {
        return ($this->age_reference_date ?? $this->starts_on ?? now())->copy();
    }

    public function trackForAge(int $age): ?ProgramEditionTrack
    {
        return $this->tracks
            ->first(fn (ProgramEditionTrack $track) => $track->acceptsAge($age));
    }

    /**
     * Required custom-field keys — what "profile completed" is measured against.
     *
     * @return array<int, string>
     */
    public function requiredFieldKeys(): array
    {
        return collect($this->registration_fields ?? [])
            ->filter(fn (array $field) => (bool) ($field['required'] ?? false))
            ->pluck('key')
            ->values()
            ->all();
    }
}
