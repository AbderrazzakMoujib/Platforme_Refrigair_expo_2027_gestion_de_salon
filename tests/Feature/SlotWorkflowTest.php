<?php

namespace Tests\Feature;

use App\Enums\SlotRequestStatus;
use App\Enums\SlotStatus;
use App\Models\SlotRequest;
use App\Models\User;
use App\Notifications\SlotConfirmedNotification;
use App\Notifications\SlotEveReminderNotification;
use App\Notifications\SlotRequestRejectedNotification;
use App\Notifications\SlotSupportReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SlotFixtures;
use Tests\TestCase;

class SlotWorkflowTest extends TestCase
{
    use RefreshDatabase, SlotFixtures;

    private function requestPayload(array $overrides = []): array
    {
        return $overrides + [
            'type' => 'conference',
            'title' => 'Les fluides frigorigènes naturels',
            'summary' => "Ligne 1\nLigne 2\nLigne 3",
            'speakers' => 'Sara El Amrani, directrice technique',
            'first_choice_slot_id' => $this->slot('C3-1')->id,
        ];
    }

    public function test_planning_is_reserved_to_commercials(): void
    {
        $this->seedSlots();
        $exhibitor = $this->exhibitor('ClimaTech');

        $this->get(route('commercial.slots.index'))->assertRedirect(route('login'));
        $this->actingAs($exhibitor->user)->get(route('commercial.slots.index'))->assertForbidden();
        // L'admin consulte via son propre espace, pas via les routes commerciales.
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('commercial.slots.index'))->assertForbidden();

        $this->actingAs($this->commercial())
            ->get(route('commercial.slots.index'))
            ->assertOk()
            ->assertSee('C2-1')
            ->assertSee('Media')
            ->assertSee('Pause 12:30 – 14:00');
    }

    public function test_exhibitor_request_is_accepted_and_confirmed(): void
    {
        Notification::fake();
        $this->seedSlots();
        $exhibitor = $this->exhibitor('ClimaTech');

        $this->actingAs($exhibitor->user)
            ->get(route('exhibitor.slots.index'))
            ->assertOk()
            ->assertSee('Réserver un créneau')
            ->assertSee('Comment ça marche ?')
            ->assertSee('45 min présentation')
            // Les noms des autres exposants ne sont jamais affichés.
            ->assertDontSee('Media')
            ->assertDontSee('FIRST THERM');

        // Un seul créneau par demande : un éventuel 2e choix envoyé est ignoré.
        $this->post(route('exhibitor.slots.request'), $this->requestPayload(['second_choice_slot_id' => $this->slot('C3-2')->id]))
            ->assertRedirect(route('exhibitor.slots.index'))
            ->assertSessionHasNoErrors();

        $request = SlotRequest::firstOrFail();
        $this->assertSame(SlotRequestStatus::Pending, $request->status);
        $this->assertSame($this->slot('C3-1')->id, $request->first_choice_slot_id);
        $this->assertNull($request->second_choice_slot_id);

        // Le créneau demandé apparaît comme « Demandé » et ne peut plus être re-sélectionné.
        $this->get(route('exhibitor.slots.index'))
            ->assertOk()
            ->assertViewHas('requestedSlotIds', [$this->slot('C3-1')->id]);

        $this->actingAs($this->commercial())
            ->patch(route('commercial.slot-requests.accept', $request), ['slot_id' => $this->slot('C3-1')->id])
            ->assertSessionHasNoErrors();

        $slot = $this->slot('C3-1');
        $this->assertSame(SlotStatus::Reserved, $slot->status);
        $this->assertSame($exhibitor->id, $slot->exhibitor_id);
        $this->assertSame('Les fluides frigorigènes naturels', $slot->title);
        $this->assertSame($slot->id, $request->fresh()->assigned_slot_id);

        Notification::assertSentTo($exhibitor->user, SlotConfirmedNotification::class);
    }

    public function test_summary_is_limited_to_five_lines(): void
    {
        $this->seedSlots();
        $exhibitor = $this->exhibitor('ClimaTech');

        $this->actingAs($exhibitor->user)
            ->post(route('exhibitor.slots.request'), $this->requestPayload(['summary' => "1\n2\n3\n4\n5\n6"]))
            ->assertSessionHasErrors('summary');
    }

    public function test_request_can_be_rejected_with_a_reason(): void
    {
        Notification::fake();
        $this->seedSlots();
        $exhibitor = $this->exhibitor('ClimaTech');
        $request = $exhibitor->slotRequests()->create($this->requestPayload() + ['status' => 'pending']);

        $this->actingAs($this->commercial());

        $this->patch(route('commercial.slot-requests.reject', $request), [])
            ->assertSessionHasErrors('rejection_reason');

        $this->patch(route('commercial.slot-requests.reject', $request), ['rejection_reason' => 'Sujet hors thématique'])
            ->assertSessionHasNoErrors();

        $this->assertSame(SlotRequestStatus::Rejected, $request->fresh()->status);
        Notification::assertSentTo($exhibitor->user, SlotRequestRejectedNotification::class);

        // Une demande traitée ne peut plus être acceptée.
        $this->patch(route('commercial.slot-requests.accept', $request), ['slot_id' => $this->slot('C2-1')->id])
            ->assertForbidden();
    }

    public function test_assigning_a_slot_from_the_planning_notifies_the_exhibitor(): void
    {
        Notification::fake();
        $this->seedSlots();
        $exhibitor = $this->exhibitor('ClimaTech');

        $this->actingAs($this->commercial())
            ->put(route('commercial.slots.update', $this->slot('L2-1')), ['status' => 'reserved', 'exhibitor_id' => $exhibitor->id])
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($exhibitor->user, SlotConfirmedNotification::class);
    }

    public function test_support_upload_is_limited_to_the_owner_of_a_confirmed_slot(): void
    {
        Storage::fake('local');
        $this->seedSlots();
        $owner = $this->exhibitor('ClimaTech');
        $other = $this->exhibitor('FroidPlus');

        $slot = $this->slot('C2-1');
        $slot->update(['status' => SlotStatus::Option, 'exhibitor_id' => $owner->id]);

        $pdf = fn () => UploadedFile::fake()->create('presentation.pdf', 500, 'application/pdf');

        // Option : pas encore confirmé.
        $this->actingAs($owner->user)
            ->post(route('exhibitor.slots.support.store', $slot), ['support' => $pdf()])
            ->assertForbidden();

        $slot->update(['status' => SlotStatus::Reserved]);

        $this->actingAs($other->user)
            ->post(route('exhibitor.slots.support.store', $slot), ['support' => $pdf()])
            ->assertForbidden();

        $this->actingAs($owner->user)
            ->post(route('exhibitor.slots.support.store', $slot), ['support' => UploadedFile::fake()->create('notes.docx', 100)])
            ->assertSessionHasErrors('support');

        $this->post(route('exhibitor.slots.support.store', $slot), ['support' => UploadedFile::fake()->create('trop-lourd.pdf', 21000, 'application/pdf')])
            ->assertSessionHasErrors('support');

        $this->post(route('exhibitor.slots.support.store', $slot), ['support' => $pdf()])
            ->assertSessionHasNoErrors();

        $slot->refresh();
        $this->assertNotNull($slot->support_path);
        $this->assertSame('presentation.pdf', $slot->support_original_name);
        Storage::disk('local')->assertExists($slot->support_path);

        $this->get(route('exhibitor.slots.support.download', $slot))->assertOk();
        $this->actingAs($other->user)->get(route('exhibitor.slots.support.download', $slot))->assertForbidden();
        $this->actingAs($this->commercial())->get(route('commercial.slots.support', $slot))->assertOk();
    }

    public function test_reminders_are_sent_once_at_d_minus_7_and_the_day_before(): void
    {
        Notification::fake();
        $this->seedSlots();
        $exhibitor = $this->exhibitor('ClimaTech');
        $slot = $this->slot('C2-1'); // mercredi 3 février 2027
        $slot->update(['status' => SlotStatus::Reserved, 'exhibitor_id' => $exhibitor->id]);

        // J-8 : trop tôt.
        $this->travelTo('2027-01-26 09:00');
        $this->artisan('slots:send-reminders')->assertSuccessful();
        Notification::assertNothingSentTo($exhibitor->user);

        // J-7 : rappel support, une seule fois.
        $this->travelTo('2027-01-27 09:00');
        $this->artisan('slots:send-reminders')->assertSuccessful();
        $this->artisan('slots:send-reminders')->assertSuccessful();
        Notification::assertSentToTimes($exhibitor->user, SlotSupportReminderNotification::class, 1);

        // Veille : test technique, une seule fois.
        $this->travelTo('2027-02-02 09:00');
        $this->artisan('slots:send-reminders')->assertSuccessful();
        $this->artisan('slots:send-reminders')->assertSuccessful();
        Notification::assertSentToTimes($exhibitor->user, SlotEveReminderNotification::class, 1);
    }

    public function test_csv_and_program_exports(): void
    {
        $this->seedSlots();
        $this->actingAs($this->commercial());

        $csv = $this->get(route('commercial.slots.export.csv'))->assertOk();
        $content = $csv->streamedContent();
        $this->assertStringContainsString('C2-2;"Salle de conférence";03/02/2027;10:30;11:30;1500,00;Réservé;Media', $content);
        $this->assertStringContainsString('C2-1;"Salle de conférence";03/02/2027;09:30;10:30;1500,00;Disponible', $content);

        foreach (['fr' => 'Réservé', 'en' => 'Booked'] as $locale => $booked) {
            $response = $this->get(route('commercial.slots.export.pdf', $locale))->assertOk();

            if (class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
                $response->assertHeader('content-type', 'application/pdf');
            } else {
                $response->assertSee($booked)->assertSee('Media');
            }
        }

        $this->get(route('commercial.slots.export.pdf', 'en'))->assertSee('Available');
    }
}
