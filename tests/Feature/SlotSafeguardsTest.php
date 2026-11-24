<?php

namespace Tests\Feature;

use App\Enums\SlotStatus;
use App\Models\SlotRequest;
use App\Notifications\SlotConfirmedNotification;
use App\Notifications\SlotSupportReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SlotFixtures;
use Tests\TestCase;

class SlotSafeguardsTest extends TestCase
{
    use RefreshDatabase, SlotFixtures;

    public function test_freeing_a_slot_removes_the_previous_holder_support_and_reminders(): void
    {
        Storage::fake('local');
        Notification::fake();
        $this->seedSlots();
        $commercial = $this->commercial();
        $first = $this->exhibitor('ClimaTech', true, $commercial);
        $second = $this->exhibitor('FroidPlus', true, $commercial);
        $slot = $this->slot('C2-1');

        $slot->update(['status' => SlotStatus::Reserved, 'exhibitor_id' => $first->id, 'support_reminder_sent_at' => now()]);

        $this->actingAs($first->user)
            ->post(route('exhibitor.slots.support.store', $slot), ['support' => UploadedFile::fake()->create('deck.pdf', 100, 'application/pdf')])
            ->assertSessionHasNoErrors();
        $oldPath = $slot->fresh()->support_path;
        Storage::disk('local')->assertExists($oldPath);

        // Libération puis attribution à un autre exposant.
        $this->actingAs($commercial)->put(route('commercial.slots.update', $slot), ['status' => 'available'])->assertSessionHasNoErrors();
        $this->put(route('commercial.slots.update', $slot), ['status' => 'reserved', 'exhibitor_id' => $second->id])->assertSessionHasNoErrors();

        $slot->refresh();
        $this->assertSame($second->id, $slot->exhibitor_id);
        $this->assertNull($slot->support_path);
        $this->assertNull($slot->support_reminder_sent_at);
        Storage::disk('local')->assertMissing($oldPath);

        // Le nouveau titulaire ne peut pas récupérer l'ancien support…
        $this->actingAs($second->user)->get(route('exhibitor.slots.support.download', $slot))->assertForbidden();

        // …et recevra bien son propre rappel J-7.
        $this->travelTo('2027-01-27 09:00');
        $this->artisan('slots:send-reminders')->assertSuccessful();
        Notification::assertSentTo($second->user, SlotSupportReminderNotification::class);
    }

    public function test_updating_details_of_the_same_holder_keeps_the_support(): void
    {
        Storage::fake('local');
        $this->seedSlots();
        $owner = $this->exhibitor('ClimaTech');
        $slot = $this->slot('C2-1');
        $slot->update(['status' => SlotStatus::Reserved, 'exhibitor_id' => $owner->id]);

        $this->actingAs($owner->user)
            ->post(route('exhibitor.slots.support.store', $slot), ['support' => UploadedFile::fake()->create('deck.pdf', 100, 'application/pdf')]);
        $path = $slot->fresh()->support_path;

        // Passage en payé + ajout d'un titre : même titulaire, rien n'est effacé.
        $this->actingAs($this->commercial())
            ->put(route('commercial.slots.update', $slot), ['status' => 'paid', 'exhibitor_id' => $owner->id, 'title' => 'Nouveau titre'])
            ->assertSessionHasNoErrors();

        $this->assertSame($path, $slot->fresh()->support_path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_an_exhibitor_cannot_send_twice_the_same_pending_request(): void
    {
        $this->seedSlots();
        $exhibitor = $this->exhibitor('ClimaTech');
        $payload = [
            'type' => 'conference',
            'title' => 'Présentation',
            'summary' => 'Résumé',
            'speakers' => 'Intervenant',
            'first_choice_slot_id' => $this->slot('C2-1')->id,
            'second_choice_slot_id' => $this->slot('C2-2')->id,
        ];

        $this->actingAs($exhibitor->user);
        $this->post(route('exhibitor.slots.request'), $payload)->assertSessionHasNoErrors();
        $this->post(route('exhibitor.slots.request'), $payload)->assertSessionHasErrors('first_choice_slot_id');

        $this->assertSame(1, SlotRequest::count());

        // Un autre exposant peut toujours demander ce créneau.
        $this->actingAs($this->exhibitor('FroidPlus')->user)
            ->post(route('exhibitor.slots.request'), $payload)
            ->assertSessionHasNoErrors();
        $this->assertSame(2, SlotRequest::count());
    }

    public function test_csv_neutralises_formulas_typed_by_users(): void
    {
        $this->seedSlots();
        $commercial = $this->commercial();
        $this->slot('C2-1')->update([
            'status' => SlotStatus::Reserved,
            'exhibitor_name' => 'Client',
            'commercial_id' => $commercial->id,
            'title' => '=HYPERLINK("http://evil.test","Cliquez")',
            'speakers' => '+212 600 000 000',
        ]);

        $content = $this->actingAs($commercial)
            ->get(route('commercial.slots.export.csv'))
            ->streamedContent();

        $this->assertStringContainsString('"\'=HYPERLINK(""http://evil.test"",""Cliquez"")"', $content);
        $this->assertStringContainsString("'+212 600 000 000", $content);
        $this->assertStringNotContainsString(';=HYPERLINK', $content);
    }

    public function test_opening_a_conference_notification_leads_to_the_exhibitor_page(): void
    {
        $this->seedSlots();
        $exhibitor = $this->exhibitor('ClimaTech');
        $slot = $this->slot('C2-1');
        $slot->update(['status' => SlotStatus::Reserved, 'exhibitor_id' => $exhibitor->id]);

        // Notification réelle (pas de fake) : enregistrée en base, file « sync » en test.
        $exhibitor->user->notify(new SlotConfirmedNotification($slot));
        $notification = $exhibitor->user->notifications()->firstOrFail();

        $this->actingAs($exhibitor->user)
            ->post(route('exhibitor.notifications.open', $notification->id))
            ->assertRedirect(route('exhibitor.slots.index'));

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
