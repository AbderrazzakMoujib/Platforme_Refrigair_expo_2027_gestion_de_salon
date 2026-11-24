<?php

namespace App\Enums;

enum SlotStatus: string
{
    case Available = 'available';
    case Option = 'option';
    case Reserved = 'reserved';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Option => 'Option',
            self::Reserved => 'Réservé',
            self::Paid => 'Payé',
        };
    }

    /** Libellé public du programme : une option bloque le créneau, il n'est donc pas présenté comme libre. */
    public function publicLabel(string $locale = 'fr'): string
    {
        $booked = $this !== self::Available;

        return $locale === 'en'
            ? ($booked ? 'Booked' : 'Available')
            : ($booked ? 'Réservé' : 'Disponible');
    }

    /** Le créneau n'est plus attribuable à un autre exposant. */
    public function isTaken(): bool
    {
        return $this !== self::Available;
    }

    /** Compte dans les ventes et le chiffre d'affaires (l'option est exclue). */
    public function isSold(): bool
    {
        return in_array($this, [self::Reserved, self::Paid], true);
    }

    /** Classes Tailwind de la tuile du planning. */
    public function tileClasses(): string
    {
        return match ($this) {
            self::Available => 'border-emerald-200 bg-emerald-50 text-emerald-900 hover:border-emerald-400',
            self::Option => 'border-amber-200 bg-amber-50 text-amber-900 hover:border-amber-400',
            self::Reserved => 'border-blue-200 bg-blue-50 text-blue-900 hover:border-blue-400',
            self::Paid => 'border-violet-200 bg-violet-50 text-violet-900 hover:border-violet-400',
        };
    }

    public function pillClasses(): string
    {
        return match ($this) {
            self::Available => 'bg-emerald-100 text-emerald-700',
            self::Option => 'bg-amber-100 text-amber-700',
            self::Reserved => 'bg-blue-100 text-blue-700',
            self::Paid => 'bg-violet-100 text-violet-700',
        };
    }
}
