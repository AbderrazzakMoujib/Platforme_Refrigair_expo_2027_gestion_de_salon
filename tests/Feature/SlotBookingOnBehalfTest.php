<?php

namespace Tests\Feature;

use App\Enums\SlotStatus;
use App\Models\User;
use App\Notifications\SlotConfirmedNotification;
use App\Notifications\SlotEveReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SlotFixtures;
use Tests\TestCase;

/** Le commercial réserve à la place d'un exposant ou d'une personne non exposante. */
class SlotBookingOnBehalfTest extends TestCase
{
    use RefreshDatabase, SlotFixtures;

    private function book(string $code, array $data)
    {
        $slot = $this->slot($code);

        return $this->put(route('commercial.slots.update', $slot), $data + ['slot_id' => $slot->id]);
    }

    public function test_commercial_books_a_slot_for_a_registered_exhibitor(): void
    {
        Notification::fake();
        $this->seedSlots();
        $commercial = $this->commercial();
        $exhibitor = $this->exhibitor('ClimaTech', commercial: $commercial);

        $this->actingAs($commercial);

        // Exposant non choisi : refusé.
        $this->book('C3-1', ['status' => 'reserved', 'holder_type' => 'exhibitor'])
            ->assertSessionHasErrors('exhibitor_id', null, 'slot');

        // Un nom saisi par erreur dans l'autre panneau est ignoré.
        $this->book('C3-1', [
            'status' => 'reserved',
            'holder_type' => 'exhibitor',
            'exhibitor_id' => $exhibitor->id,
            'exhibitor_name' => 'Ignoré',
            'contact_email' => 'ignore@test.ma',
            'title' => 'Froid industriel',
        ])->assertSessionHasNoErrors();

        $slot = $this->slot('C3-1');
        $this->assertSame($exhibitor->id, $slot->exhibitor_id);
        $this->assertNull($slot->exhibitor_name);
        $this->assertNull($slot->contact_email);
        $this->assertFalse($slot->is_external);
        Notification::assertSentTo($exhibitor->user, SlotConfirmedNotification::class);
    }

    public function test_commercial_books_a_slot_for_someone_who_is_not_an_exhibitor(): void
    {
        Notification::fake();
        $this->seedSlots();

        $this->actingAs($this->commercial());

        $this->book('L2-1', ['status' => 'reserved', 'holder_type' => 'external'])
            ->assertSessionHasErrors('exhibitor_name', null, 'slot');

        $this->book('L2-1', ['status' => 'reserved', 'holder_type' => 'external', 'exhibitor_name' => 'Atlas', 'contact_email' => 'pas-un-email'])
            ->assertSessionHasErrors('contact_email', null, 'slot');

        $this->book('L2-1', [
            'status' => 'reserved',
            'holder_type' => 'external',
            'exhibitor_id' => 999, // ignoré pour une personne externe
            'exhibitor_name' => 'Bureau d\'études Atlas Froid',
            'contact_name' => 'Youssef Benali',
            'contact_email' => 'y.benali@atlas.test',
            'contact_phone' => '+212 661 00 00 00',
        ])->assertSessionHasNoErrors();

        $slot = $this->slot('L2-1');
        $this->assertNull($slot->exhibitor_id);
        $this->assertTrue($slot->is_external);
        $this->assertSame('Bureau d\'études Atlas Froid', $slot->holder_name);
        $this->assertSame('y.benali@atlas.test', $slot->contact_email);

        // Confirmation envoyée par e-mail au contact, sans lien vers un espace exposant.
        Notification::assertSentOnDemand(
            SlotConfirmedNotification::class,
            function (SlotConfirmedNotification $notification, array $channels, AnonymousNotifiable $notifiable) {
                $mail = $notification->toMail($notifiable);

                return $notifiable->routes['mail'] === 'y.benali@atlas.test'
                    && $channels === ['mail']
                    && $mail->actionUrl === null;
            }
        );

        // Visible côté admin, avec ses coordonnées.
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.slots.index'))
            ->assertSee('Atlas Froid');
    }

    public function test_saving_a_free_slot_with_a_holder_reserves_it_even_if_status_was_left_on_available(): void
    {
        $this->seedSlots();
        $commercial = $this->commercial();
        $exhibitor = $this->exhibitor('ClimaTech', commercial: $commercial);
        $this->actingAs($commercial);

        // Personne non exposante, statut resté sur « Disponible ».
        $this->book('L2-1', ['status' => 'available', 'holder_type' => 'external', 'exhibitor_name' => 'Atlas Froid'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Créneau L2-1 réservé pour Atlas Froid (Réservé).');

        $this->assertSame(SlotStatus::Reserved, $this->slot('L2-1')->status);
        $this->assertSame('Atlas Froid', $this->slot('L2-1')->holder_name);

        // Même chose pour un exposant inscrit.
        $this->book('C3-2', ['status' => 'available', 'holder_type' => 'exhibitor', 'exhibitor_id' => $exhibitor->id])
            ->assertSessionHasNoErrors();
        $this->assertSame(SlotStatus::Reserved, $this->slot('C3-2')->status);

        // Une option choisie volontairement est respectée.
        $this->book('C3-3', ['status' => 'option', 'holder_type' => 'external', 'exhibitor_name' => 'Prospect'])
            ->assertSessionHasNoErrors();
        $this->assertSame(SlotStatus::Option, $this->slot('C3-3')->status);

        // Sur un créneau déjà pris, « Disponible » le libère toujours.
        $this->book('L2-1', ['status' => 'available', 'holder_type' => 'external', 'exhibitor_name' => 'Atlas Froid'])
            ->assertSessionHas('status', 'Créneau L2-1 libéré.');
        $this->assertSame(SlotStatus::Available, $this->slot('L2-1')->status);
        $this->assertNull($this->slot('L2-1')->holder_name);
    }

    public function test_external_holder_without_email_is_not_notified(): void
    {
        Notification::fake();
        $this->seedSlots();

        $this->actingAs($this->commercial())
            ->book('L2-1', ['status' => 'reserved', 'holder_type' => 'external', 'exhibitor_name' => 'Sans e-mail'])
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_commercial_can_upload_the_support_on_behalf_of_the_holder(): void
    {
        Storage::fake('local');
        $this->seedSlots();
        $commercial = $this->commercial();
        $pdf = fn () => UploadedFile::fake()->create('programme.pdf', 300, 'application/pdf');

        $slot = $this->slot('C2-2');
        $slot->update(['status' => SlotStatus::Reserved, 'exhibitor_name' => 'Media', 'commercial_id' => $commercial->id]);
        $this->assertSame($commercial->id, (int) $slot->fresh()->commercial_id);
        $this->assertTrue($commercial->can('uploadSupport', $slot->fresh()));

        $this->actingAs($commercial)
            ->post(route('commercial.slots.support.store', $slot), ['support' => $pdf(), 'slot_id' => $slot->id])
            ->assertRedirect(route('commercial.slots.index'))
            ->assertSessionHasNoErrors();

        $slot->refresh();
        $this->assertSame('programme.pdf', $slot->support_original_name);
        Storage::disk('local')->assertExists($slot->support_path);

        // Mauvais format refusé.
        $this->post(route('commercial.slots.support.store', $slot), ['support' => UploadedFile::fake()->create('notes.txt', 10)])
            ->assertSessionHasErrors('support');

        // Pas de support sur un créneau seulement en option.
        $option = $this->slot('C2-1');
        $option->update(['status' => SlotStatus::Option, 'exhibitor_name' => 'Prospect']);
        $this->post(route('commercial.slots.support.store', $option), ['support' => $pdf()])
            ->assertForbidden();

        // L'admin consulte mais ne dépose pas.
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('commercial.slots.support.store', $slot), ['support' => $pdf()])
            ->assertForbidden();
    }

    public function test_external_holder_with_email_receives_reminders(): void
    {
        Notification::fake();
        $this->seedSlots();

        $this->slot('C3-1')->update([
            'status' => SlotStatus::Reserved,
            'exhibitor_name' => 'Atlas',
            'contact_email' => 'contact@atlas.test',
        ]);

        $this->travelTo('2027-02-03 09:00'); // veille du jeudi 4
        $this->artisan('slots:send-reminders')->assertSuccessful();

        Notification::assertSentOnDemand(
            SlotEveReminderNotification::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'contact@atlas.test'
        );
    }

    public function test_planning_explains_how_to_book_for_someone_else(): void
    {
        $this->seedSlots();

        $this->actingAs($this->commercial())
            ->get(route('commercial.slots.index'))
            ->assertOk()
            ->assertSee('Exposant inscrit')
            ->assertSee('Société / contact externe');
    }
}
