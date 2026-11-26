<?php

namespace App\Enums;

enum SlotRequestStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Accepted => 'Acceptée',
            self::Rejected => 'Refusée',
            self::Cancelled => 'Annulée',
        };
    }

    public function pillClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-700',
            self::Accepted => 'bg-emerald-100 text-emerald-700',
            self::Rejected => 'bg-red-100 text-red-700',
            self::Cancelled => 'bg-slate-100 text-slate-700',
        };
    }
}
