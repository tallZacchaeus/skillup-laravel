<?php

namespace App\Models\Programs;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramAttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_registration_id',
        'attended_on',
        'present',
        'recorded_by_user_id',
    ];

    protected $casts = [
        'attended_on' => 'date',
        'present' => 'boolean',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(ProgramRegistration::class, 'program_registration_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
