<?php

namespace App\Models\Programs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProgramCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'serial',
        'program_registration_id',
        'recipient_name',
        'program_title',
        'issued_on',
        'pdf_path',
        'metadata',
    ];

    protected $casts = [
        'issued_on' => 'date',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProgramCertificate $certificate) {
            $certificate->uuid ??= (string) Str::uuid();
            $certificate->serial ??= strtoupper(Str::random(4).'-'.Str::random(4).'-'.Str::random(4));
        });
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(ProgramRegistration::class, 'program_registration_id');
    }
}
