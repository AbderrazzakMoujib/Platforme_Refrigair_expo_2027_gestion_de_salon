<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialInvitation extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'user_id',
        'role',
        'token_hash',
        'expires_at',
        'sent_at',
        'accepted_at',
        'delivery_status',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function statusFor(User $user): string
    {
        if (! $user->is_active) {
            return 'Désactivé';
        }

        if ($user->password !== null) {
            return 'Actif';
        }

        $invitation = $user->relationLoaded('latestInvitation')
            ? $user->latestInvitation
            : $user->invitations()->latest('id')->first();

        if (! $invitation) {
            return 'À inviter';
        }

        if ($invitation->accepted_at !== null) {
            return 'Activé';
        }

        if ($invitation->delivery_status === self::STATUS_REVOKED) {
            return 'Invitation révoquée';
        }

        if ($invitation->expires_at->isPast()) {
            return 'Invitation expirée';
        }

        return match ($invitation->delivery_status) {
            self::STATUS_PENDING, self::STATUS_SENDING => 'Envoi en attente',
            self::STATUS_FAILED => 'Échec d’envoi',
            default => 'Invitation envoyée',
        };
    }
}
