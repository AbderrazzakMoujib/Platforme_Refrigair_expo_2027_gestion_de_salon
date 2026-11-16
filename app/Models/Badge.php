<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Badge extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exhibitor_id',
        'first_name',
        'last_name',
        'function',
        'email',
        'phone',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Badge $badge): void {
            if (! $badge->qr_token) {
                $badge->qr_token = (string) Str::uuid();
            }

            if (! $badge->status) {
                $badge->status = 'active';
            }
        });
    }

    public function exhibitor(): BelongsTo
    {
        return $this->belongsTo(Exhibitor::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(BadgeActivityLog::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getReferenceAttribute(): string
    {
        return 'REF-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && ! $this->trashed();
    }
}
