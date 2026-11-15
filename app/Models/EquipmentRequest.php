<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Notifications\NewEquipmentRequestNotification;
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
        $admins = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->get();

        Notification::send(
            $admins,
            new NewEquipmentRequestNotification($order)
        );
    });
   }

}
