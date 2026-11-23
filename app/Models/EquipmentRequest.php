<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Notifications\NewEquipmentRequestNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Notification;

class EquipmentRequest extends Model
{
    protected $fillable = [
        'exhibitor_id',
        'status',
        'total_amount',
        'notes',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function exhibitor(): BelongsTo
    {
        return $this->belongsTo(Exhibitor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EquipmentRequestItem::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    protected static function booted(): void
    {
        static::created(function (EquipmentRequest $order): void {

            $exhibitor = $order->exhibitor;

            // Tous les administrateurs actifs.
            $admins = User::query()
                ->where('role', UserRole::Admin->value)
                ->where('is_active', true)
                ->get();

            // Commercial responsable de cet exposant.
            $commercial = $exhibitor?->commercial;

            $recipients = $admins;

            if (
                $commercial
                && $commercial->role === UserRole::Commercial
                && $commercial->is_active
            ) {
                $recipients->push($commercial);
            }

            // Éviter tout doublon.
            $recipients = $recipients
                ->unique('id')
                ->values();

            if ($recipients->isNotEmpty()) {
                Notification::send(
                    $recipients,
                    new NewEquipmentRequestNotification($order)
                );
            }
        });
    }
}
