<?php

namespace Tests\Feature;

use App\Enums\SlotStatus;
use App\Models\Slot;
use App\Services\SlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SlotFixtures;
use Tests\TestCase;

class SlotRevenueTest extends TestCase
{
    use RefreshDatabase, SlotFixtures;

    private function stats(): array
    {
        return app(SlotService::class)->stats();
    }

    /**
     * Crée un scénario de réservations uniquement
     * dans la base temporaire utilisée par les tests.
     *
     * 5 conférences x 1 500 € = 7 500 €
     * 8 lounges x 1 000 € = 8 000 €
     *
     * Total : 13 réservations / 15 500 € HT.
     */
    private function createRevenueScenario(): void
    {
        $codes = [
            // Mercredi 3 février : 5 conférences
            'C2-2',
            'C2-3',
            'C2-4',
            'C2-5',
            'C2-6',

            // Mardi 2 février : 3 lounges
            'L1-1',
            'L1-2',
            'L1-3',

            // Mercredi 3 février : 2 lounges
            'L2-1',
            'L2-3',

            // Jeudi 4 février : 3 lounges
            'L3-1',
            'L3-2',
            'L3-3',
        ];

        $updated = Slot::query()
            ->whereIn('code', $codes)
            ->update([
                'status' => SlotStatus::Reserved->value,
                'exhibitor_name' => 'Réservation de test',
            ]);

        $this->assertSame(13, $updated);
    }

    public function test_initial_catalog_has_zero_revenue(): void
    {
        $this->seedSlots();

        $stats = $this->stats();

        $this->assertSame(
            23,
            $stats['total']['total']
        );

        $this->assertSame(
            0,
            $stats['total']['sold']
        );

        $this->assertSame(
            23,
            $stats['total']['available']
        );

        $this->assertSame(
            0.0,
            $stats['total']['revenue']
        );

        $this->assertSame(
            0.0,
            $stats['total']['collected']
        );

        $this->assertSame(
            0.0,
            $stats['total']['fill_rate']
        );

        $this->assertSame(
            0,
            $stats['spaces']['conference']['sold']
        );

        $this->assertSame(
            0,
            $stats['spaces']['lounge']['sold']
        );
    }

    public function test_revenue_of_confirmed_bookings(): void
    {
        $this->seedSlots();

        $this->createRevenueScenario();

        $stats = $this->stats();

        $this->assertSame(
            23,
            $stats['total']['total']
        );

        $this->assertSame(
            13,
            $stats['total']['sold']
        );

        $this->assertSame(
            15500.0,
            $stats['total']['revenue']
        );

        $this->assertSame(
            0.0,
            $stats['total']['collected']
        );

        $this->assertSame(
            5,
            $stats['spaces']['conference']['sold']
        );

        $this->assertSame(
            12,
            $stats['spaces']['conference']['total']
        );

        $this->assertSame(
            7500.0,
            $stats['spaces']['conference']['revenue']
        );

        $this->assertSame(
            8,
            $stats['spaces']['lounge']['sold']
        );

        $this->assertSame(
            11,
            $stats['spaces']['lounge']['total']
        );

        $this->assertSame(
            8000.0,
            $stats['spaces']['lounge']['revenue']
        );
    }

    public function test_options_are_excluded_and_paid_slots_are_collected(): void
    {
        $this->seedSlots();

        $this->createRevenueScenario();

        // C2-1 : nouveau prospect, ne compte pas
        // dans le chiffre d'affaires.
        $this->slot('C2-1')->update([
            'status' => SlotStatus::Option,
            'exhibitor_name' => 'Prospect',
        ]);

        // Paiements sur deux réservations existantes.
        $this->slot('C2-2')->update([
            'status' => SlotStatus::Paid,
        ]);

        $this->slot('L1-1')->update([
            'status' => SlotStatus::Paid,
        ]);

        $stats = $this->stats();

        $this->assertSame(
            13,
            $stats['total']['sold']
        );

        $this->assertSame(
            1,
            $stats['total']['options']
        );

        $this->assertSame(
            15500.0,
            $stats['total']['revenue']
        );

        $this->assertSame(
            2500.0,
            $stats['total']['collected']
        );

        $this->assertSame(
            1500.0,
            $stats['spaces']['conference']['collected']
        );

        $this->assertSame(
            1000.0,
            $stats['spaces']['lounge']['collected']
        );
    }

    public function test_fill_rate_per_day(): void
    {
        $this->seedSlots();

        $this->createRevenueScenario();

        $days = collect($this->stats()['days'])
            ->keyBy(
                fn ($day) =>
                    $day['date']->toDateString()
            );

        // Mardi 2 : 3 / 3 lounges réservés.
        $this->assertSame(
            3,
            $days['2027-02-02']['total']
        );

        $this->assertSame(
            100.0,
            $days['2027-02-02']['fill_rate']
        );

        // Mercredi 3 : 5 conférences + 2 lounges.
        $this->assertSame(
            10,
            $days['2027-02-03']['total']
        );

        $this->assertSame(
            7,
            $days['2027-02-03']['sold']
        );

        $this->assertSame(
            70.0,
            $days['2027-02-03']['fill_rate']
        );

        // Jeudi 4 : 3 lounges réservés.
        $this->assertSame(
            10,
            $days['2027-02-04']['total']
        );

        $this->assertSame(
            3,
            $days['2027-02-04']['sold']
        );

        $this->assertSame(
            30.0,
            $days['2027-02-04']['fill_rate']
        );
    }

    public function test_commercial_dashboard_shows_slot_indicators(): void
    {
        $this->seedSlots();

        $this->createRevenueScenario();

        $this->actingAs($this->commercial())
            ->get(route('commercial.index'))
            ->assertOk()
            ->assertViewHas(
                'slotStats',
                fn (array $stats) =>
                    $stats['total']['revenue'] === 15500.0
            )
            ->assertSee('Conférences & Lounge des Technologies', false)
            ->assertSee('15 500 €')
            ->assertSee('70 %');
    }

    public function test_dashboard_still_renders_without_any_slot(): void
    {
        $this->actingAs($this->commercial())
            ->get(route('commercial.index'))
            ->assertOk()
            ->assertSee(
                'Aucun créneau n\'est encore configuré.',
                false
            );
    }
}
