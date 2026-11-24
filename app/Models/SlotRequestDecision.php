<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlotRequestDecision extends Model
{
    protected $fillable = [
        'slot_request_id',
        'exhibitor_id',
        'exhibitor_name',
        'request_title',
        'request_type',
        'decision',
        'reason',
        'assigned_slot_id',
        'processed_by',
        'processor_name',
        'processed_at',
    ];

    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SlotRequest::class, 'slot_request_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function assignedSlot(): BelongsTo
    {
        return $this->belongsTo(Slot::class, 'assigned_slot_id');
    }
}
