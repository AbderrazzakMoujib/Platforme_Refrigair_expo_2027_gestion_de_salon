<?php

namespace App\Support;

use App\Enums\SpaceType;

final class SlotCatalog
{
    public const CONFERENCE_PRICE = 1500;
    public const LOUNGE_PRICE = 1000;

    public const SPACES = [
        SpaceType::Conference->value => [
            'name' => 'Salle de conférence',
            'capacity' => null,
        ],
        SpaceType::Lounge->value => [
            'name' => 'Lounge des Technologies',
            'capacity' => null,
        ],
    ];

    private const SCHEDULE = [
        SpaceType::Conference->value => [
            [
                'C2',
                '2027-02-03',
                [
                    ['09:30', '10:30'],
                    ['10:30', '11:30'],
                    ['11:30', '12:30'],
                    ['14:00', '15:00'],
                    ['15:00', '16:00'],
                    ['16:00', '17:00'],
                ],
            ],
            [
                'C3',
                '2027-02-04',
                [
                    ['09:30', '10:30'],
                    ['10:30', '11:30'],
                    ['11:30', '12:30'],
                    ['14:00', '15:00'],
                    ['15:00', '16:00'],
                    ['16:00', '17:00'],
                ],
            ],
        ],

        SpaceType::Lounge->value => [
            [
                'L1',
                '2027-02-02',
                [
                    ['11:00', '13:00'],
                    ['13:00', '15:00'],
                    ['15:00', '17:00'],
                ],
            ],
            [
                'L2',
                '2027-02-03',
                [
                    ['09:30', '11:30'],
                    ['11:30', '13:30'],
                    ['13:30', '15:30'],
                    ['15:30', '17:30'],
                ],
            ],
            [
                'L3',
                '2027-02-04',
                [
                    ['09:30', '11:30'],
                    ['11:30', '13:30'],
                    ['13:30', '15:30'],
                    ['15:30', '17:30'],
                ],
            ],
        ],
    ];

    // Aucune reservation fictive initiale.
    public const INITIAL_BOOKINGS = [];

    public static function slots(): array
    {
        $slots = [];

        foreach (self::SCHEDULE as $type => $days) {
            $price = $type === SpaceType::Conference->value
                ? self::CONFERENCE_PRICE
                : self::LOUNGE_PRICE;

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

    public static function rules(string $locale = 'fr'): array
    {
        if ($locale === 'en') {
            return [
                'Conference room (3–4 February 2027): one-hour slots, 45 minutes presentation, 10 minutes Q&A and 5 minutes changeover; €1,500 excl. VAT.',
                'Technology Lounge (2–4 February 2027): two-hour slots including a table/display and a single-sided 55-inch screen; setup takes the first 10 minutes; €1,000 excl. VAT.',
                'Submit your company, speakers, title, summary (maximum five lines) and two preferred slots. Confirmation by email within 48 hours, subject to organizer processing and availability.',
                'Send presentation slides (PPT or PDF, 16:9) no later than seven days before the exhibition begins, on 26 January 2027.',
                'Speakers arrive 15 minutes before their session for the technical check.',
            ];
        }

        return [
            'Salle de conférence (3–4 février 2027) : créneaux d’une heure, 45 min de présentation, 10 min de questions-réponses, 5 min de changement ; 1 500 € HT.',
            'Lounge des Technologies (2–4 février 2027) : créneaux de deux heures, table ou présentoir et écran ultra-fin 55 pouces simple face ; les 10 premières minutes sont réservées à l’installation ; 1 000 € HT.',
            'Demande : nom de société, intervenants, titre, résumé de 5 lignes maximum et deux créneaux préférés. Confirmation par e-mail sous 48 heures, après traitement par l’organisation et sous réserve de disponibilité.',
            'Support de présentation (PPT ou PDF, format 16:9) à remettre au plus tard le 26 janvier 2027, soit 7 jours avant le salon.',
            'L’intervenant se présente 15 minutes avant son créneau pour le test technique.',
        ];
    }
}
