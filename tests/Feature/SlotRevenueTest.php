<?php

namespace Tests\Feature;

use App\Enums\SlotStatus;
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

    public function test_revenue_of_the_initial_bookings(): void
    {
        $this->seedSlots();

        $stats = $this->stats();

        $this->assertSame(23, $stats['total']['total']);
        $this->assertSame(13, $stats['total']['sold']);
        $this->assertSame(15500.0, $stats['total']['revenue']);
        $this->assertSame(0.0, $stats['total']['collected']);

        $this->assertSame(5, $stats['spaces']['conference']['sold']);
        $this->assertSame(12, $stats['spaces']['conference']['total']);
        $this->assertSame(7500.0, $stats['spaces']['conference']['revenue']);

        $this->assertSame(8, $stats['spaces']['lounge']['sold']);
        $this->assertSame(11, $stats['spaces']['lounge']['total']);
        $this->assertSame(8000.0, $stats['spaces']['lounge']['revenue']);
    }

    public function test_options_are_excluded_and_paid_slots_are_collected(): void
    {
        $this->seedSlots();

        $this->slot('C2-1')->update(['status' => SlotStatus::Option, 'exhibitor_name' => 'Prospect']);
        $this->slot('C2-2')->update(['status' => SlotStatus::Paid]);
        $this->slot('L1-1')->update(['status' => SlotStatus::Paid]);

        $stats = $this->stats();

        $this->assertSame(13, $stats['total']['sold']);
        $this->assertSame(1, $stats['total']['options']);
        $this->assertSame(15500.0, $stats['total']['revenue']);
        $this->assertSame(2500.0, $stats['total']['collected']);
        $this->assertSame(1500.0, $stats['spaces']['conference']['collected']);
        $this->assertSame(1000.0, $stats['spaces']['lounge']['collected']);
    }

    public function test_fill_rate_per_day(): void
    {
        $this->seedSlots();

        $days = collect($this->stats()['days'])->keyBy(fn ($day) => $day['date']->toDateString());

        // Mardi 2 : lounge uniquement, 3 / 3.
        $this->assertSame(3, $days['2027-02-02']['total']);
        $this->assertSame(100.0, $days['2027-02-02']['fill_rate']);

        // Mercredi 3 : 5 conférences + 2 lounge sur 10 créneaux.
        $this->assertSame(10, $days['2027-02-03']['total']);
        $this->assertSame(7, $days['2027-02-03']['sold']);
        $this->assertSame(70.0, $days['2027-02-03']['fill_rate']);

        // Jeudi 4 : 0 conférence + 3 lounge sur 10 créneaux.
        $this->assertSame(3, $days['2027-02-04']['sold']);
        $this->assertSame(30.0, $days['2027-02-04']['fill_rate']);
    }

    public function test_commercial_dashboard_shows_slot_indicators(): void
    {
        $this->seedSlots();

        $this->actingAs($this->commercial())
            ->get(route('commercial.index'))
            ->assertOk()
            ->assertViewHas('slotStats', fn (array $stats) => $stats['total']['revenue'] === 15500.0)
            ->assertSee('Conférences & Lounge des Technologies', false)
            ->assertSee('15 500 €')
            ->assertSee('70 %');
    }

    public function test_dashboard_still_renders_without_any_slot(): void
    {
        $this->actingAs($this->commercial())
            ->get(route('commercial.index'))
            ->assertOk()
            ->assertSee('Aucun créneau n\'est encore configuré.', false);
    }
}
