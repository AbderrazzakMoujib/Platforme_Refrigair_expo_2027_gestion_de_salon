<?php

namespace App\Models;

use App\Notifications\NewExhibitorNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class Exhibitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'phone',
        'stand_number',
        'area_sqm',
        'authorized_badges',
        'logo',
    ];

    protected function casts(): array
    {
        return [
            'area_sqm' => 'decimal:2',
            'authorized_badges' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Exhibitor $exhibitor): void {
            $admins = User::query()
                ->where('role', 'admin')
                ->where('is_active', true)
                ->get();

            Notification::send(
                $admins,
                new NewExhibitorNotification($exhibitor)
            );
        });
    }

    public function commercial(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function catalogProfile(): HasOne
    {
        return $this->hasOne(ExhibitorCatalogProfile::class);
    }

    public function badges(): HasMany
    {
        return $this->hasMany(Badge::class);
    }

    /** Créneaux de conférence / lounge attribués à l'exposant. */
    public function slots(): HasMany
    {
        return $this->hasMany(Slot::class);
    }

    public function slotRequests(): HasMany
    {
        return $this->hasMany(SlotRequest::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo
            ? Storage::disk('public')->url($this->logo)
            : null;
    }

    public function getInitialsAttribute(): string
    {
        return mb_strtoupper(
            mb_substr(trim($this->company_name ?? ''), 0, 2)
        ) ?: 'EX';
    }
}
