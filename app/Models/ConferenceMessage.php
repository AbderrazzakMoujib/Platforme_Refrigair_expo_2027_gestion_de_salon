<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConferenceMessage extends Model
{
    protected $fillable = [
        'slot_request_id',
        'sender_id',
        'body',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(
            SlotRequest::class,
            'slot_request_id'
        );
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
