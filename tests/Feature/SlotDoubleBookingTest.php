<?php

namespace Tests\Feature;

use App\Enums\SlotRequestStatus;
use App\Enums\SlotStatus;
use App\Models\Slot;
use App\Models\SlotRequest;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SlotFixtures;
use Tests\TestCase;

class SlotDoubleBookingTest extends TestCase
{
    use RefreshDatabase, SlotFixtures;

    private function assign(Slot $slot, array $data)
    {
        return $this->put(route('commercial.slots.update', $slot), $data + ['slot_id' => $slot->id]);
    }

    public function test_a_taken_slot_cannot_be_assigned_to_another_exhibitor(): void
    {
        $this->seedSlots();
        $commercial = $this->commercial();
        $first = $this->exhibitor('ClimaTech');
        $second = $this->exhibitor('FroidPlus');
        $slot = $this->slot('C2-1');

        $this->actingAs($commercial);

        $this->assign($slot, ['status' => 'reserved', 'exhibitor_id' => $first->id])
            ->assertRedirect(route('commercial.slots.index'))
            ->assertSessionHasNoErrors();

        $this->assign($slot, ['status' => 'reserved', 'exhibitor_id' => $second->id])
            ->assertSessionHasErrors('exhibitor_id', null, 'slot');

        $slot->refresh();
        $this->assertSame($first->id, $slot->exhibitor_id);
        $this->assertSame(SlotStatus::Reserved, $slot->status);
    }

    public function test_an_initial_booking_cannot_be_overwritten_with_a_free_name(): void
    {
        $this->seedSlots();
        $this->actingAs($this->commercial());

        $this->assign($this->slot('C2-2'), ['status' => 'option', 'exhibitor_name' => 'Concurrent'])
            ->assertSessionHasErrors('exhibitor_id', null, 'slot');

        $this->assertSame('Media', $this->slot('C2-2')->exhibitor_name);
    }

    public function test_the_same_holder_can_update_details_and_a_freed_slot_can_be_reassigned(): void
    {
        $this->seedSlots();
        $other = $this->exhibitor('FroidPlus');
        $this->actingAs($this->commercial());
        $slot = $this->slot('C2-2');

        // Même titulaire : passage en payé et ajout du titre.
        $this->assign($slot, ['status' => 'paid', 'exhibitor_name' => 'media', 'title' => 'Innovation'])
            ->assertSessionHasNoErrors();
        $this->assertSame(SlotStatus::Paid, $slot->fresh()->status);
        $this->assertSame('Innovation', $slot->fresh()->title);

        // Libération puis nouvelle attribution.
        $this->assign($slot, ['status' => 'available'])->assertSessionHasNoErrors();
        $this->assertNull($slot->fresh()->holder_name);

        $this->assign($slot, ['status' => 'reserved', 'exhibitor_id' => $other->id])->assertSessionHasNoErrors();
        $this->assertSame($other->id, $slot->fresh()->exhibitor_id);
    }

    public function test_a_non_available_status_requires_a_holder(): void
    {
        $this->seedSlots();
        $this->actingAs($this->commercial());

        $this->assign($this->slot('C2-1'), ['status' => 'reserved', 'holder_type' => 'exhibitor'])
            ->assertSessionHasErrors('exhibitor_id', null, 'slot');

        $this->assign($this->slot('C2-1'), ['status' => 'reserved', 'holder_type' => 'external'])
            ->assertSessionHasErrors('exhibitor_name', null, 'slot');

        $this->assertSame(SlotStatus::Available, $this->slot('C2-1')->status);
    }

    public function test_two_requests_on_the_same_slot_cannot_both_be_accepted(): void
    {
        $this->seedSlots();
        $commercial = $this->commercial();
        $slot = $this->slot('C2-1');

        $requests = collect(['ClimaTech', 'FroidPlus'])->map(fn (string $company) => SlotRequest::create([
            'exhibitor_id' => $this->exhibitor($company)->id,
            'type' => 'conference',
            'title' => 'Présentation '.$company,
            'summary' => 'Résumé',
            'speakers' => 'Intervenant',
            'first_choice_slot_id' => $slot->id,
        ]));

        $this->actingAs($commercial);

        $this->patch(route('commercial.slot-requests.accept', $requests[0]), ['slot_id' => $slot->id])
            ->assertSessionHasNoErrors();

        $this->patch(route('commercial.slot-requests.accept', $requests[1]), ['slot_id' => $slot->id])
            ->assertSessionHasErrors('slot_id');

        $this->assertSame($requests[0]->exhibitor_id, $slot->fresh()->exhibitor_id);
        $this->assertSame(SlotRequestStatus::Accepted, $requests[0]->fresh()->status);
        $this->assertSame(SlotRequestStatus::Pending, $requests[1]->fresh()->status);
    }

    public function test_a_request_can_only_be_accepted_on_one_of_its_choices(): void
    {
        $this->seedSlots();
        $request = SlotRequest::create([
            'exhibitor_id' => $this->exhibitor('ClimaTech')->id,
            'type' => 'conference',
            'title' => 'Présentation',
            'summary' => 'Résumé',
            'speakers' => 'Intervenant',
            'first_choice_slot_id' => $this->slot('C2-1')->id,
        ]);

        $this->actingAs($this->commercial())
            ->patch(route('commercial.slot-requests.accept', $request), ['slot_id' => $this->slot('C3-1')->id])
            ->assertSessionHasErrors('slot_id');

        $this->assertSame(SlotStatus::Available, $this->slot('C3-1')->status);
    }

    public function test_an_exhibitor_cannot_request_a_slot_that_is_already_taken(): void
    {
        $this->seedSlots();
        $exhibitor = $this->exhibitor('ClimaTech');

        $payload = [
            'type' => 'conference',
            'title' => 'Présentation',
            'summary' => 'Résumé',
            'speakers' => 'Intervenant',
        ];

        $this->actingAs($exhibitor->user);

        // Créneau déjà réservé (Media).
        $this->post(route('exhibitor.slots.request'), $payload + ['first_choice_slot_id' => $this->slot('C2-2')->id])
            ->assertSessionHasErrors('first_choice_slot_id');

        // Créneau du lounge alors que l'espace choisi est la conférence.
        $this->post(route('exhibitor.slots.request'), $payload + ['first_choice_slot_id' => $this->slot('L2-1')->id])
            ->assertSessionHasErrors('first_choice_slot_id');

        // Aucun créneau choisi.
        $this->post(route('exhibitor.slots.request'), $payload)
            ->assertSessionHasErrors('first_choice_slot_id');

        $this->assertSame(0, SlotRequest::count());
    }

    public function test_database_forbids_two_slots_at_the_same_time_in_the_same_space(): void
    {
        $this->seedSlots();
        $existing = $this->slot('C2-1');

        $this->expectException(QueryException::class);

        Slot::create([
            'space_id' => $existing->space_id,
            'code' => 'C2-99',
            'date' => '2027-02-03',
            'start_time' => $existing->start_time,
            'end_time' => $existing->end_time,
            'price_excl_tax' => 1500,
            'status' => 'available',
        ]);
    }

    public function test_slot_codes_are_unique(): void
    {
        $this->seedSlots();
        $existing = $this->slot('C2-1');

        $this->expectException(QueryException::class);

        Slot::create([
            'space_id' => $existing->space_id,
            'code' => 'C2-1',
            'date' => '2027-02-05',
            'start_time' => '09:30',
            'end_time' => '10:30',
            'price_excl_tax' => 1500,
            'status' => 'available',
        ]);
    }
}
