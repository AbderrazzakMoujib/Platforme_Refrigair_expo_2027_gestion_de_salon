<?php

namespace App\Enums;

enum SpaceType: string
{
    case Conference = 'conference';
    case Lounge = 'lounge';

    public function label(): string
    {
        return match ($this) {
            self::Conference => 'Salle de conférence',
            self::Lounge => 'Lounge des Technologies',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::Conference => 'Conference room',
            self::Lounge => 'Technology Lounge',
        };
    }
}
