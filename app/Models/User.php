<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'email',
    'phone',
    'password',
    'role',
    'is_active',
    'profile_photo',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function exhibitors(): HasMany
    {
        return $this->hasMany(
            Exhibitor::class,
            'commercial_id'
        );
    }

    public function exhibitorProfile(): HasOne
    {
        return $this->hasOne(Exhibitor::class);
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo
            ? Storage::disk('public')->url($this->profile_photo)
            : null;
    }

    public function getInitialsAttribute(): string
    {
        return collect(
            preg_split('/\s+/', trim($this->name))
        )
            ->filter()
            ->take(2)
            ->map(
                fn (string $part) =>
                    mb_strtoupper(mb_substr($part, 0, 1))
            )
            ->implode('') ?: 'U';
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }
}
