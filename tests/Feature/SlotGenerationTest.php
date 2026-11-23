<?php

namespace Tests\Feature;

use App\Enums\SlotStatus;
use App\Enums\SpaceType;
use App\Models\Slot;
use App\Models\Space;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SlotFixtures;
use Tests\TestCase;

class SlotGenerationTest extends TestCase
{
    use RefreshDatabase, SlotFixtures;

    public function test_seeder_creates_both_spaces_and_all_slots(): void
    {
        $this->seedSlots();

        $this->assertSame(2, Space::count());
        $this->assertSame(23, Slot::count());

        $conference = Space::where('type', SpaceType::Conference)->firstOrFail();
        $lounge = Space::where('type', SpaceType::Lounge)->firstOrFail();

        $this->assertSame(12, $conference->slots()->count());
        $this->assertSame(11, $lounge->slots()->count());

        $this->assertEqualsCanonicalizing(
            ['C2-1', 'C2-2', 'C2-3', 'C2-4', 'C2-5', 'C2-6', 'C3-1', 'C3-2', 'C3-3', 'C3-4', 'C3-5', 'C3-6'],
            $conference->slots()->pluck('code')->all()
        );
        $this->assertEqualsCanonicalizing(
            ['L1-1', 'L1-2', 'L1-3', 'L2-1', 'L2-2', 'L2-3', 'L2-4', 'L3-1', 'L3-2', 'L3-3', 'L3-4'],
            $lounge->slots()->pluck('code')->all()
        );
    }

    public function test_conference_slots_follow_the_official_schedule_and_price(): void
    {
        $this->seedSlots();

        $conference = Slot::whereHas('space', fn ($q) => $q->where('type', 'conference'))->get();

        // Uniquement mercredi 3 et jeudi 4 février 2027.
        $this->assertEqualsCanonicalizing(
            ['2027-02-03', '2027-02-04'],
            $conference->map(fn (Slot $slot) => $slot->date->toDateString())->unique()->values()->all()
        );
        $this->assertTrue($conference->every(fn (Slot $slot) => (float) $slot->price_excl_tax === 1500.0));

        $this->assertSame('09:30 – 10:30', $this->slot('C2-1')->time_range);
        $this->assertSame('11:30 – 12:30', $this->slot('C2-3')->time_range);
        // Après la pause de 12:30 à 14:00.
        $this->assertSame('14:00 – 15:00', $this->slot('C2-4')->time_range);
        $this->assertSame('16:00 – 17:00', $this->slot('C3-6')->time_range);
        $this->assertTrue($this->slot('C3-1')->date->isThursday());
    }

    public function test_lounge_slots_follow_the_official_schedule_and_price(): void
    {
        $this->seedSlots();

        $lounge = Slot::whereHas('space', fn ($q) => $q->where('type', 'lounge'))->get();

        $this->assertTrue($lounge->every(fn (Slot $slot) => (float) $slot->price_excl_tax === 1000.0));

        $this->assertTrue($this->slot('L1-1')->date->isTuesday());
        $this->assertSame('11:00 – 13:00', $this->slot('L1-1')->time_range);
        $this->assertSame('15:00 – 17:00', $this->slot('L1-3')->time_range);
        $this->assertSame('09:30 – 11:30', $this->slot('L2-1')->time_range);
        $this->assertSame('15:30 – 17:30', $this->slot('L3-4')->time_range);
        $this->assertSame('2027-02-04', $this->slot('L3-4')->date->toDateString());
    }

    public function test_initial_bookings_link_existing_exhibitors_or_keep_the_name(): void
    {
        // Exposant déjà en base (casse différente) : il doit être relié.
        $ampf = $this->exhibitor('ampf', withAccount: false);

        $this->seedSlots();

        $this->assertSame(13, Slot::where('status', SlotStatus::Reserved)->count());
        $this->assertSame(10, Slot::where('status', SlotStatus::Available)->count());

        $this->assertSame($ampf->id, $this->slot('L2-3')->exhibitor_id);
        $this->assertNull($this->slot('L2-3')->exhibitor_name);
        $this->assertSame($ampf->id, $this->slot('L3-3')->exhibitor_id);

        // Exposant absent de la base : nom stocké dans exhibitor_name.
        $media = $this->slot('C2-2');
        $this->assertNull($media->exhibitor_id);
        $this->assertSame('Media', $media->exhibitor_name);
        $this->assertSame('FIRST THERM', $this->slot('L1-3')->holder_name);

        $this->assertSame(SlotStatus::Available, $this->slot('C2-1')->status);
        $this->assertNull($this->slot('C2-1')->holder_name);
    }

    public function test_seeder_can_run_twice_without_duplicates_or_overwriting_changes(): void
    {
        $this->seedSlots();

        $this->slot('C2-1')->update(['status' => SlotStatus::Option, 'exhibitor_name' => 'Nouveau client']);
        $this->slot('C2-2')->update(['status' => SlotStatus::Paid]);

        $this->seedSlots();

        $this->assertSame(2, Space::count());
        $this->assertSame(23, Slot::count());
        $this->assertSame(SlotStatus::Option, $this->slot('C2-1')->status);
        $this->assertSame(SlotStatus::Paid, $this->slot('C2-2')->status);
    }
}
