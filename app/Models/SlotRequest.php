<?php

namespace App\Models;

use App\Enums\SlotRequestStatus;
use App\Enums\SpaceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlotRequest extends Model
{
    protected $fillable = [
        'exhibitor_id',
        'type',
        'title',
        'summary',
        'speakers',
        'first_choice_slot_id',
        'second_choice_slot_id',
        'status',
        'rejection_reason',
        'assigned_slot_id',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => SpaceType::class,
            'status' => SlotRequestStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    public function exhibitor(): BelongsTo
    {
        return $this->belongsTo(Exhibitor::class);
    }

    public function firstChoice(): BelongsTo
    {
        return $this->belongsTo(Slot::class, 'first_choice_slot_id');
    }

    public function secondChoice(): BelongsTo
    {
        return $this->belongsTo(Slot::class, 'second_choice_slot_id');
    }

    public function assignedSlot(): BelongsTo
    {
        return $this->belongsTo(Slot::class, 'assigned_slot_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(SlotRequestDecision::class);
    }

    /** Identifiants des créneaux proposés par l'exposant. */
    public function choiceIds(): array
    {
        return array_values(array_filter([
            $this->first_choice_slot_id,
            $this->second_choice_slot_id,
        ]));
    }
}
