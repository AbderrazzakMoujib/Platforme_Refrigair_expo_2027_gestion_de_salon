<?php

namespace App\Models;

use App\Enums\SlotStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slot extends Model
{
    protected $fillable = [
        'space_id',
        'code',
        'date',
        'start_time',
        'end_time',
        'price_excl_tax',
        'status',
        'exhibitor_id',
        'exhibitor_name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'title',
        'summary',
        'speakers',
        'support_path',
        'support_original_name',
        'support_uploaded_at',
        'notes',
        'support_reminder_sent_at',
        'eve_reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'price_excl_tax' => 'decimal:2',
            'status' => SlotStatus::class,
            'support_uploaded_at' => 'datetime',
            'support_reminder_sent_at' => 'datetime',
            'eve_reminder_sent_at' => 'datetime',
        ];
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function exhibitor(): BelongsTo
    {
        return $this->belongsTo(Exhibitor::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('date')->orderBy('start_time')->orderBy('code');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', SlotStatus::Available->value);
    }

    /** Nom affiché du titulaire : exposant en base, sinon nom libre. */
    public function getHolderNameAttribute(): ?string
    {
        return $this->exhibitor?->company_name ?? $this->exhibitor_name;
    }

    /** Titulaire saisi par le commercial, sans fiche exposant. */
    public function getIsExternalAttribute(): bool
    {
        return $this->exhibitor_id === null && filled($this->exhibitor_name);
    }

    public function getStartLabelAttribute(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    public function getEndLabelAttribute(): string
    {
        return substr((string) $this->end_time, 0, 5);
    }

    public function getTimeRangeAttribute(): string
    {
        return $this->start_label.' – '.$this->end_label;
    }

    /** Libellé complet, utilisé dans les listes déroulantes et les e‑mails. */
    public function getFullLabelAttribute(): string
    {
        return $this->code.' · '.$this->date->locale('fr')->isoFormat('dddd D MMMM').' · '.$this->time_range;
    }
}
