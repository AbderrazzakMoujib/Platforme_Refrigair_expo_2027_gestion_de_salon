<?php

namespace App\Support;

use App\Enums\SpaceType;

/**
 * Référentiel des créneaux Conférences & Lounge des Technologies
 * de Refrigair Expo 2027 (2 au 4 février 2027, OFEC Casablanca).
 */
final class SlotCatalog
{
    public const CONFERENCE_PRICE = 1500;

    public const LOUNGE_PRICE = 1000;

    public const SPACES = [
        SpaceType::Conference->value => ['name' => 'Salle de conférence', 'capacity' => null],
        SpaceType::Lounge->value => ['name' => 'Lounge des Technologies', 'capacity' => null],
    ];

    /** Horaires par espace, par jour : [préfixe du code, date, [[début, fin], …]]. */
    private const SCHEDULE = [
        SpaceType::Conference->value => [
            ['C2', '2027-02-03', [['09:30', '10:30'], ['10:30', '11:30'], ['11:30', '12:30'], ['14:00', '15:00'], ['15:00', '16:00'], ['16:00', '17:00']]],
            ['C3', '2027-02-04', [['09:30', '10:30'], ['10:30', '11:30'], ['11:30', '12:30'], ['14:00', '15:00'], ['15:00', '16:00'], ['16:00', '17:00']]],
        ],
        SpaceType::Lounge->value => [
            ['L1', '2027-02-02', [['11:00', '13:00'], ['13:00', '15:00'], ['15:00', '17:00']]],
            ['L2', '2027-02-03', [['09:30', '11:30'], ['11:30', '13:30'], ['13:30', '15:30'], ['15:30', '17:30']]],
            ['L3', '2027-02-04', [['09:30', '11:30'], ['11:30', '13:30'], ['13:30', '15:30'], ['15:30', '17:30']]],
        ],
    ];

    /** Réservations connues au lancement du module (statut « réservé »). */
    public const INITIAL_BOOKINGS = [
        'C2-2' => 'Media',
        'C2-3' => 'Ebm Papst',
        'C2-4' => 'Daikoo',
        'C2-5' => 'Froidel',
        'C2-6' => 'FIRST THERM',
        'L1-1' => 'RES',
        'L1-2' => 'RES',
        'L1-3' => 'FIRST THERM',
        'L2-2' => 'RES',
        'L2-3' => 'AMPF',
        'L3-2' => 'RES',
        'L3-3' => 'AMPF',
        'L3-4' => 'RES',
    ];

    /**
     * Liste à plat de tous les créneaux.
     *
     * @return list<array{type: string, code: string, date: string, start_time: string, end_time: string, price_excl_tax: int}>
     */
    public static function slots(): array
    {
        $slots = [];

        foreach (self::SCHEDULE as $type => $days) {
            $price = $type === SpaceType::Conference->value ? self::CONFERENCE_PRICE : self::LOUNGE_PRICE;

            foreach ($days as [$prefix, $date, $ranges]) {
                foreach ($ranges as $index => [$start, $end]) {
                    $slots[] = [
                        'type' => $type,
                        'code' => $prefix.'-'.($index + 1),
                        'date' => $date,
                        'start_time' => $start,
                        'end_time' => $end,
                        'price_excl_tax' => $price,
                    ];
                }
            }
        }

        return $slots;
    }

    /** Règles de réservation affichées dans le programme PDF. */
    public static function rules(string $locale = 'fr'): array
    {
        if ($locale === 'en') {
            return [
                'Conference room (Wednesday 3 and Thursday 4 February 2027): 1-hour slots — 45 min presentation + 10 min Q&A + 5 min changeover. Price: €1,500 excl. VAT per slot.',
                'Technology Lounge (2 to 4 February 2027, continuous): 2-hour slots. Price: €1,000 excl. VAT per slot. Includes a table or display stand, a 55" single-sided screen and a product sheet; 10 minutes of set-up before the slot.',
                'Booking requests: topic, 5-line summary, speakers and the chosen slot. Slots are allocated in order of confirmation.',
                'The presentation (PPT, PPTX or PDF, 20 MB max.) must be sent at least 7 days before the slot.',
                'Speakers must arrive 15 minutes before their slot for the technical check.',
            ];
        }

        return [
            'Salle de conférence (mercredi 3 et jeudi 4 février 2027) : créneaux de 1 h — 45 min de présentation + 10 min de questions-réponses + 5 min de changement. Prix : 1 500 € HT par créneau.',
            'Lounge des Technologies (du 2 au 4 février 2027, en continu) : créneaux de 2 h. Prix : 1 000 € HT par créneau. Inclus : table ou présentoir, écran 55" simple face, fiche produit ; 10 min d\'installation avant le créneau.',
            'Demande de réservation : sujet, résumé de 5 lignes, intervenants et le créneau choisi. Les créneaux sont attribués par ordre de confirmation.',
            'Le support de présentation (PPT, PPTX ou PDF, 20 Mo max.) doit être envoyé au plus tard 7 jours avant le créneau.',
            'Les intervenants se présentent 15 minutes avant leur créneau pour le test technique.',
        ];
    }
}
