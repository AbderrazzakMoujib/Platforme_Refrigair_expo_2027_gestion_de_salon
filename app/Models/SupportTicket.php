<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'exhibitor_id',
        'commercial_id',
        'subject',
        'category',
        'status',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'commercial_last_seen_message_id' => 'integer',
        ];
    }

    public function exhibitor(): BelongsTo
    {
        return $this->belongsTo(Exhibitor::class);
    }

    public function commercial(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }

    // Compter uniquement les tickets avec des messages entrants non lus.
    public function scopeUnreadForCommercial(
        Builder $query,
        int $commercialId
    ): Builder {
        return $query
            ->where('commercial_id', $commercialId)
            ->whereHas(
                'exhibitor',
                fn (Builder $q) => $q->where(
                    'commercial_id',
                    $commercialId
                )
            )
            ->whereHas('messages', function (Builder $q): void {
                $q->whereColumn(
                    'support_messages.user_id',
                    '!=',
                    'support_tickets.commercial_id'
                )->where(function (Builder $new): void {
                    $new->whereNull(
                        'support_tickets.commercial_last_seen_message_id'
                    )->orWhereColumn(
                        'support_messages.id',
                        '>',
                        'support_tickets.commercial_last_seen_message_id'
                    );
                });
            });
    }

    // Quand le commercial ouvre le ticket, ses messages sont marqués lus.
    public function markIncomingMessagesAsReadByCommercial(): void
    {
        $latestIncomingId = $this->messages()
            ->where('user_id', '!=', $this->commercial_id)
            ->max('id');

        if ($latestIncomingId !== null) {
            self::query()
                ->whereKey($this->id)
                ->where(function (Builder $query) use ($latestIncomingId): void {
                    $query
                        ->whereNull('commercial_last_seen_message_id')
                        ->orWhere(
                            'commercial_last_seen_message_id',
                            '<',
                            $latestIncomingId
                        );
                })
                ->update([
                    'commercial_last_seen_message_id' => $latestIncomingId,
                ]);

            $this->refresh();
        }
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'En cours',
            'resolved' => 'Résolue',
            default => 'En attente',
        };
    }
}
