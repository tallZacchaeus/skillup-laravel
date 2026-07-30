<?php

namespace App\Filament\Instructor\Concerns;

trait ScopesInstructorProfile
{
    protected static function instructorProfileId(): ?int
    {
        return auth()->user()?->instructorProfile?->id;
    }
}
