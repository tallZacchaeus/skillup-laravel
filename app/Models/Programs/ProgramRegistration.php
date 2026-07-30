<?php

namespace App\Models\Programs;

use App\Enums\ProgramRegistrationStatus;
use App\Models\Catalog\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class ProgramRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'program_edition_id',
        'program_edition_track_id',
        'user_id',
        'order_id',
        'guardian_name',
        'guardian_email',
        'guardian_phone',
        'guardian_whatsapp',
        'participant_name',
        'participant_dob',
        'participant_gender',
        'status',
        'email_verification_token',
        'email_verification_otp',
        'email_verification_expires_at',
        'email_verified_at',
        'email_invalid_at',
        'seat_held_until',
        'resume_token',
        'profile_completed_at',
        'custom_fields',
        'emergency_contact_name',
        'emergency_contact_phone',
        'medical_notes',
        'authorized_pickups',
        'first_aid_consent',
        'media_consent',
        'guardian_consent_at',
        'safeguarding_purged_at',
        'sibling_group_uuid',
        'source',
        'utm',
        'metadata',
    ];

    protected $casts = [
        'status' => ProgramRegistrationStatus::class,
        'participant_dob' => 'date',
        'email_verification_expires_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'email_invalid_at' => 'datetime',
        'seat_held_until' => 'datetime',
        'profile_completed_at' => 'datetime',
        'guardian_consent_at' => 'datetime',
        'safeguarding_purged_at' => 'datetime',
        'custom_fields' => 'array',
        'medical_notes' => 'encrypted',
        'authorized_pickups' => 'encrypted:array',
        'first_aid_consent' => 'boolean',
        'media_consent' => 'boolean',
        'utm' => 'array',
        'metadata' => 'array',
    ];

    protected $hidden = [
        'email_verification_token',
        'email_verification_otp',
        'resume_token',
        'medical_notes',
        'authorized_pickups',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProgramRegistration $registration) {
            $registration->uuid ??= (string) Str::uuid();
            $registration->sibling_group_uuid ??= (string) Str::uuid();
            $registration->resume_token ??= Str::random(48);
        });
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(ProgramEdition::class, 'program_edition_id');
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(ProgramEditionTrack::class, 'program_edition_track_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(ProgramCertificate::class);
    }

    public function attendanceRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgramAttendanceRecord::class);
    }

    public function attendanceDays(): int
    {
        return $this->attendanceRecords()->where('present', true)->count();
    }

    public function participantAge(): int
    {
        return (int) $this->participant_dob->diffInYears($this->edition->ageReferenceDate());
    }

    public function holdsSeat(): bool
    {
        return $this->status->isPaidOrBeyond()
            || ($this->seat_held_until !== null && $this->seat_held_until->isFuture());
    }

    public function isProfileComplete(): bool
    {
        if (! $this->guardian_consent_at || ! $this->emergency_contact_name || ! $this->emergency_contact_phone) {
            return false;
        }

        $answers = $this->custom_fields ?? [];

        foreach ($this->edition->requiredFieldKeys() as $key) {
            if (blank($answers[$key] ?? null)) {
                return false;
            }
        }

        return true;
    }

    public function isCertificateEligible(): bool
    {
        return $this->status === ProgramRegistrationStatus::Completed
            && $this->profile_completed_at !== null;
    }
}
