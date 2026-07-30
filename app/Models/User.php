<?php

namespace App\Models;

use App\Models\Catalog\AdminProfile;
use App\Models\Catalog\AuditLog;
use App\Models\Catalog\CorporateLearner;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\InstructorProfile;
use App\Models\Catalog\LearnerProfile;
use App\Models\Catalog\Order;
use App\Models\Catalog\Product;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function learnerProfile(): HasOne
    {
        return $this->hasOne(LearnerProfile::class);
    }

    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class);
    }

    public function instructorProfile(): HasOne
    {
        return $this->hasOne(InstructorProfile::class);
    }

    public function wishlistProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'wishlist_items')->withTimestamps();
    }

    public function cart(): HasOne
    {
        return $this->hasOne(\App\Models\Catalog\Cart::class);
    }

    public function corporateLearners(): HasMany
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

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->hasAnyRole(['Admin', 'Super Admin'])) {
            return true;
        }

        return match ($panel->getId()) {
            'learner' => $this->hasRole('Learner'),
            'corporate' => $this->hasRole('Corporate'),
            'instructor' => $this->hasRole('Instructor'),
            default => false,
        };
    }
}
