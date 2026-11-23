<?php

namespace Database\Seeders;

use App\Enums\SlotStatus;
use App\Models\Exhibitor;
use App\Models\Slot;
use App\Models\Space;
use App\Support\SlotCatalog;
use Illuminate\Database\Seeder;

/**
 * Crée les 2 espaces, les 23 créneaux et les réservations actuelles.
 *
 * Peut être relancé sans risque : les créneaux existants (et les
 * modifications faites depuis l'interface) ne sont pas écrasés.
 */
class ConferenceLoungeSeeder extends Seeder
{
    public function run(): void
    {
        $spaces = [];

        foreach (SlotCatalog::SPACES as $type => $attributes) {
            $spaces[$type] = Space::query()->firstOrCreate(['type' => $type], $attributes);
        }

        // Correspondance nom → exposant existant, sans tenir compte de la casse.
        $exhibitors = Exhibitor::query()
            ->get(['id', 'company_name'])
            ->keyBy(fn (Exhibitor $exhibitor) => mb_strtolower(trim($exhibitor->company_name)));

        foreach (SlotCatalog::slots() as $definition) {
            $slot = Slot::query()->firstOrCreate(
                ['code' => $definition['code']],
                [
                    'space_id' => $spaces[$definition['type']]->id,
                    'date' => $definition['date'],
                    'start_time' => $definition['start_time'],
                    'end_time' => $definition['end_time'],
                    'price_excl_tax' => $definition['price_excl_tax'],
                    'status' => SlotStatus::Available,
                ]
            );

            $holder = SlotCatalog::INITIAL_BOOKINGS[$slot->code] ?? null;

            // On n'applique la réservation initiale que sur un créneau encore libre.
            if ($holder === null || $slot->status !== SlotStatus::Available) {
                continue;
            }

            $exhibitor = $exhibitors->get(mb_strtolower($holder));

            $slot->update([
                'status' => SlotStatus::Reserved,
                'exhibitor_id' => $exhibitor?->id,
                'exhibitor_name' => $exhibitor ? null : $holder,
            ]);
        }
    }
}
