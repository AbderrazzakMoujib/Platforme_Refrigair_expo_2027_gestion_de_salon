<?php

namespace Tests\Feature;

use App\Enums\SlotStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SlotFixtures;
use Tests\TestCase;

class AdminSlotsTest extends TestCase
{
    use RefreshDatabase, SlotFixtures;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_admin_sees_who_booked_each_slot_and_the_revenue(): void
    {
        $this->seedSlots();
        $commercial = $this->commercial();
        $exhibitor = $this->exhibitor('ClimaTech', commercial: $commercial);
        $this->slot('C3-1')->update(['status' => SlotStatus::Paid, 'exhibitor_id' => $exhibitor->id, 'title' => 'Froid durable']);

        $this->actingAs($this->admin())
            ->get(route('admin.slots.index'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['total']['revenue'] === 17000.0 && $stats['total']['collected'] === 1500.0)
            ->assertViewHas('holders', function ($holders) {
                $res = $holders->firstWhere('name', 'RES');

                return $res['codes'] === ['L1-1', 'L1-2', 'L2-2', 'L3-2', 'L3-4']
                    && $res['lounge'] === 5
                    && $res['revenue'] === 5000.0;
            })
            ->assertSee('Qui a réservé ?')
            ->assertSee('ClimaTech')
            ->assertSee('Froid durable')
            ->assertSee('Commercial : '.$commercial->name)
            ->assertSee('FIRST THERM')
            ->assertSee('17 000 € HT');
    }

    public function test_admin_can_filter_slots(): void
    {
        $this->seedSlots();
        $this->actingAs($this->admin());

        $this->get(route('admin.slots.index', ['search' => 'media']))
            ->assertOk()
            ->assertViewHas('slots', fn ($slots) => $slots->pluck('code')->all() === ['C2-2']);

        $this->get(route('admin.slots.index', ['space' => 'conference', 'status' => 'available']))
            ->assertViewHas('slots', fn ($slots) => $slots->count() === 7);

        $this->get(route('admin.slots.index', ['date' => '2027-02-02']))
            ->assertViewHas('slots', fn ($slots) => $slots->pluck('code')->all() === ['L1-1', 'L1-2', 'L1-3']);

        $this->get(route('admin.slots.index', ['status' => 'taken']))
            ->assertViewHas('slots', fn ($slots) => $slots->count() === 13);
    }

    public function test_admin_can_export_and_download_supports_but_not_modify(): void
    {
        Storage::fake('local');
        $this->seedSlots();
        $exhibitor = $this->exhibitor('ClimaTech');
        $slot = $this->slot('C2-1');
        $slot->update(['status' => SlotStatus::Reserved, 'exhibitor_id' => $exhibitor->id]);

        $this->actingAs($exhibitor->user)
            ->post(route('exhibitor.slots.support.store', $slot), ['support' => UploadedFile::fake()->create('deck.pdf', 200, 'application/pdf')])
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin());

        $this->get(route('admin.slots.export.csv'))->assertOk();
        $this->get(route('admin.slots.export.pdf', 'en'))->assertOk();
        $this->get(route('admin.slots.support', $slot))->assertOk();

        // Lecture seule : pas d'accès aux actions de gestion.
        $this->put(route('commercial.slots.update', $slot), ['status' => 'available'])->assertForbidden();
        $this->assertSame(SlotStatus::Reserved, $slot->fresh()->status);
    }

    public function test_admin_page_is_forbidden_to_other_roles(): void
    {
        $this->seedSlots();

        $this->get(route('admin.slots.index'))->assertRedirect(route('login'));
        $this->actingAs($this->commercial())->get(route('admin.slots.index'))->assertForbidden();
        $this->actingAs($this->exhibitor('ClimaTech')->user)->get(route('admin.slots.index'))->assertForbidden();
    }

    public function test_opening_the_request_form_url_redirects_the_exhibitor(): void
    {
        $exhibitor = $this->exhibitor('ClimaTech');

        $this->actingAs($exhibitor->user)
            ->get('/exposant/conferences/demandes')
            ->assertRedirect(route('exhibitor.slots.index'));
    }
}
