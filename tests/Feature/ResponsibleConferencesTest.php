<?php

namespace Tests\Feature;

use App\Enums\SlotRequestStatus;
use App\Enums\SlotStatus;
use App\Models\Exhibitor;
use App\Models\Slot;
use App\Models\SlotRequest;
use App\Models\User;
use App\Mail\CommercialInvitationMail;
use App\Notifications\SlotConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\SlotFixtures;
use Tests\TestCase;

class ResponsibleConferencesTest extends TestCase
{
    use RefreshDatabase, SlotFixtures;

    private function manager(): User
    {
        return User::factory()->create([
            'role' => 'responsable_conferences',
            'is_active' => true,
        ]);
    }

    private function requestFor(Exhibitor $exhibitor, string $first = 'C3-1', string $second = 'C3-2', string $title = 'Sujet privé'): SlotRequest
    {
        return $exhibitor->slotRequests()->create([
            'type' => 'conference',
            'title' => $title,
            'summary' => 'Résumé de la demande',
            'speakers' => 'Intervenant',
            'first_choice_slot_id' => $this->slot($first)->id,
            'second_choice_slot_id' => $this->slot($second)->id,
            'status' => SlotRequestStatus::Pending,
        ]);
    }

    public function test_guest_sees_login_form_and_is_redirected_from_manager_space(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertViewIs('auth.login')
            ->assertSee('Responsable Conf');

        $this->get(route('responsable-conferences.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_manager_login_uses_the_existing_authentication_flow(): void
    {
        $manager = $this->manager();
        $password = 'StrongPass123!';
        $manager->forceFill(['password' => Hash::make($password)])->save();

        $this->post(route('login.store'), [
            'email' => $manager->email,
            'password' => $password,
        ])->assertRedirect(route('responsable-conferences.dashboard'));

        $this->assertAuthenticatedAs($manager);
        $this->get(route('responsable-conferences.dashboard'))->assertOk();
    }

    public function test_only_admin_can_create_manage_and_revoke_manager_accounts(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $commercial = $this->commercial();

        $this->actingAs($commercial)
            ->get(route('admin.responsables-conferences.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.responsables-conferences.store'), [
                'name' => 'Responsable Essai',
                'email' => 'manager@example.test',
            ])
            ->assertRedirect(route('admin.responsables-conferences.index'));

        $manager = User::query()->where('email', 'manager@example.test')->firstOrFail();
        $this->assertSame('responsable_conferences', $manager->role->value);
        $this->assertTrue($manager->is_active);
        $this->assertNull($manager->password);
        $activationToken = null;
        Mail::assertSent(CommercialInvitationMail::class, function (CommercialInvitationMail $mail) use (&$activationToken): bool {
            preg_match('~/account/activation/([A-Za-z0-9]+)~', $mail->activationUrl, $matches);
            $activationToken = $matches[1] ?? null;
            return $mail->role === 'responsable_conferences';
        });
        $this->post(route('logout'));
        $this->post(route('account-activation.accept', $activationToken), [
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ])->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('StrongPass123!', $manager->fresh()->password));
        $this->actingAs($admin);

        $this->put(route('admin.responsables-conferences.update', $manager), [
            'name' => 'Responsable Modifié',
            'email' => 'manager.updated@example.test',
        ])->assertRedirect(route('admin.responsables-conferences.index'));

        $this->put(route('admin.responsables-conferences.password', $manager), [
            'password' => 'NewStrongPass123!',
            'password_confirmation' => 'NewStrongPass123!',
        ])->assertSessionHas('status');
        $this->assertTrue(Hash::check('NewStrongPass123!', $manager->fresh()->password));

        $this->patch(route('admin.responsables-conferences.access', $manager))
            ->assertSessionHas('status');
        $this->assertFalse($manager->fresh()->is_active);
        $this->actingAs($manager->fresh())
            ->get(route('responsable-conferences.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_manager_has_an_independent_space_and_cannot_enter_other_spaces(): void
    {
        $this->seedSlots();
        $manager = $this->manager();

        $this->actingAs($manager)
            ->get(route('responsable-conferences.dashboard'))
            ->assertOk();
        $this->get(route('responsable-conferences.planning'))->assertOk();
        $this->get(route('responsable-conferences.requests.index'))->assertOk();
        $this->get(route('responsable-conferences.history'))->assertOk();
        $this->get(route('admin.index'))->assertForbidden();
        $this->get(route('commercial.index'))->assertForbidden();
        $this->get(route('exposant.index'))->assertForbidden();
    }

    public function test_commercial_sees_only_owned_requests_and_anonymized_competitor_bookings(): void
    {
        $this->seedSlots();
        $commercial = $this->commercial();
        $otherCommercial = $this->commercial();
        $ownExhibitor = $this->exhibitor('Société Propre', commercial: $commercial);
        $otherExhibitor = $this->exhibitor('Société Confidentielle', commercial: $otherCommercial);
        $ownRequest = $this->requestFor($ownExhibitor, 'C3-1', 'C3-2', 'Sujet de mon client');
        $this->requestFor($otherExhibitor, 'C3-3', 'C3-4', 'Sujet confidentiel');
        $this->assertSame($commercial->id, $ownExhibitor->commercial_id);
        $this->assertSame($otherCommercial->id, $otherExhibitor->commercial_id);
        $this->assertSame(1, SlotRequest::query()->whereHas('exhibitor', fn ($query) => $query->where('commercial_id', $commercial->id))->count());

        $ownSlot = $this->slot('C2-1');
        $ownSlot->update(['status' => SlotStatus::Reserved, 'exhibitor_id' => $ownExhibitor->id, 'title' => 'Réservation propre']);
        $otherSlot = $this->slot('C2-3');
        $otherSlot->update(['status' => SlotStatus::Reserved, 'exhibitor_id' => $otherExhibitor->id, 'title' => 'Sujet client secret']);
        $externalSlot = $this->slot('L2-1');
        $externalSlot->update(['status' => SlotStatus::Reserved, 'commercial_id' => $otherCommercial->id, 'exhibitor_name' => 'Contact externe privé', 'contact_email' => 'private@example.test']);

        $this->actingAs($commercial)
            ->get(route('commercial.slot-requests.index'))
            ->assertOk()
            ->assertSee($ownRequest->title)
            ->assertDontSee('Sujet confidentiel')
            ->assertDontSee('Société Confidentielle');

        $planningResponse = $this->get(route('commercial.slots.index'));
        $planningResponse->assertViewHas('recentRequests', fn ($requests) => $requests->count() === 1 && $requests->first()->exhibitor?->company_name === 'Société Propre');
        $planningResponse->assertOk()
            ->assertSee('Réservation propre')
            ->assertDontSee('Sujet client secret')
            ->assertDontSee('Société Confidentielle')
            ->assertDontSee('Contact externe privé')
            ->assertDontSee('private@example.test');

        $csv = $this->get(route('commercial.slots.export.csv'))->assertOk()->streamedContent();
        $this->assertStringNotContainsString('Société Confidentielle', $csv);
        $this->assertStringNotContainsString('private@example.test', $csv);

        $this->patch(route('responsable-conferences.requests.accept', $ownRequest), ['slot_id' => $this->slot('C3-1')->id])
            ->assertForbidden();
        $this->put(route('commercial.slots.update', $otherSlot), ['status' => 'available', 'holder_type' => 'exhibitor'])
            ->assertForbidden();
    }

    public function test_commercial_can_submit_a_private_request_for_its_exhibitor_only(): void
    {
        $this->seedSlots();
        $commercial = $this->commercial();
        $otherCommercial = $this->commercial();
        $own = $this->exhibitor('Own Company', commercial: $commercial);
        $other = $this->exhibitor('Other Company', commercial: $otherCommercial);
        $payload = [
            'exhibitor_id' => $own->id,
            'type' => 'conference',
            'title' => 'Présentation commerciale',
            'summary' => 'Résumé en cinq lignes maximum.',
            'speakers' => 'Intervenant A',
            'first_choice_slot_id' => $this->slot('C3-1')->id,
            'second_choice_slot_id' => $this->slot('C3-2')->id,
        ];

        $this->actingAs($commercial)
            ->post(route('commercial.slot-requests.store'), $payload)
            ->assertRedirect(route('commercial.slot-requests.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('slot_requests', [
            'exhibitor_id' => $own->id,
            'status' => 'pending',
        ]);
        $this->assertSame(SlotStatus::Available, $this->slot('C3-1')->fresh()->status);

        $this->post(route('commercial.slot-requests.store'), [...$payload, 'exhibitor_id' => $other->id])
            ->assertForbidden();
    }

    public function test_manager_confirms_once_and_conflict_keeps_the_later_request_pending(): void
    {
        Notification::fake();
        $this->seedSlots();
        $firstExhibitor = $this->exhibitor('First Company');
        $secondExhibitor = $this->exhibitor('Second Company');
        $first = $this->requestFor($firstExhibitor, 'C3-1', 'C3-2', 'First request');
        $second = $this->requestFor($secondExhibitor, 'C3-1', 'C3-3', 'Second request');
        $manager = $this->manager();

        $this->actingAs($manager)
            ->patch(route('responsable-conferences.requests.accept', $first), ['slot_id' => $this->slot('C3-1')->id])
            ->assertSessionHasNoErrors();

        $first->refresh();
        $this->assertSame(SlotRequestStatus::Accepted, $first->status);
        $this->assertSame($manager->id, $first->processed_by);
        $this->assertNotNull($first->processed_at);
        $this->assertDatabaseHas('slot_request_decisions', [
            'slot_request_id' => $first->id,
            'decision' => 'accepted',
            'processed_by' => $manager->id,
            'assigned_slot_id' => $this->slot('C3-1')->id,
        ]);
        $this->assertSame(SlotStatus::Reserved, $this->slot('C3-1')->fresh()->status);
        $this->assertSame($firstExhibitor->commercial_id, $this->slot('C3-1')->fresh()->commercial_id);

        $this->patch(route('responsable-conferences.requests.accept', $second), ['slot_id' => $this->slot('C3-1')->id])
            ->assertSessionHasErrorsIn('slot-request', 'slot_id');

        $this->assertSame(SlotRequestStatus::Pending, $second->fresh()->status);
        $this->assertDatabaseCount('slot_request_decisions', 1);
        $this->assertSame($firstExhibitor->id, $this->slot('C3-1')->fresh()->exhibitor_id);
        Notification::assertSentTo($firstExhibitor->user, SlotConfirmedNotification::class);

        $this->get(route('responsable-conferences.confirmed'))
            ->assertOk()
            ->assertSee('First request')
            ->assertDontSee('Second request');
        $this->get(route('responsable-conferences.history'))
            ->assertOk()
            ->assertSee('First request');
    }

    public function test_manager_can_edit_and_release_only_manual_reservations_without_moving_the_slot(): void
    {
        $this->seedSlots();
        $manager = $this->manager();
        $slot = $this->slot('L2-1');
        $slot->update([
            'status' => SlotStatus::Reserved,
            'exhibitor_name' => 'Société externe',
            'contact_name' => 'Contact Initial',
            'contact_email' => 'initial@example.test',
            'title' => 'Sujet initial',
            'summary' => 'Résumé initial',
            'speakers' => 'Intervenant initial',
            'notes' => 'Note initiale',
            'commercial_id' => null,
        ]);
        $fixed = [$slot->space_id, $slot->date->toDateString(), $slot->start_time, $slot->end_time];

        $this->actingAs($this->commercial())
            ->put(route('responsable-conferences.reservations.update', $slot), ['title' => 'Tentative non autorisée'])
            ->assertForbidden();
        $this->delete(route('responsable-conferences.reservations.destroy', $slot))->assertForbidden();

        $this->actingAs($manager)
            ->put(route('responsable-conferences.reservations.update', $slot), [
                'exhibitor_name' => 'Société mise à jour',
                'contact_name' => 'Nouveau Contact',
                'contact_email' => 'new@example.test',
                'contact_phone' => '+212600000000',
                'title' => 'Sujet actualisé',
                'summary' => 'Résumé actualisé',
                'speakers' => 'Nouvel intervenant',
                'notes' => 'Nouvelle note',
            ])
            ->assertRedirect(route('responsable-conferences.planning'))
            ->assertSessionHas('status');

        $slot->refresh();
        $this->assertSame('Sujet actualisé', $slot->title);
        $this->assertSame('Nouveau Contact', $slot->contact_name);
        $this->assertSame('Nouvel intervenant', $slot->speakers);
        $this->assertSame($fixed, [$slot->space_id, $slot->date->toDateString(), $slot->start_time, $slot->end_time]);

        $this->delete(route('responsable-conferences.reservations.destroy', $slot))
            ->assertRedirect(route('responsable-conferences.planning'));
        $slot->refresh();
        $this->assertSame(SlotStatus::Available, $slot->status);
        $this->assertNull($slot->exhibitor_name);
        $this->assertSame($fixed, [$slot->space_id, $slot->date->toDateString(), $slot->start_time, $slot->end_time]);
    }

    public function test_manager_cancels_an_accepted_request_releases_slot_and_keeps_request_conversation_and_audit(): void
    {
        $this->seedSlots();
        $manager = $this->manager();
        $exhibitor = $this->exhibitor('Société à annuler');
        $slot = $this->slot('C3-1');
        $slotRequest = $this->requestFor($exhibitor, 'C3-1', 'C3-2');
        $slot->update(['status' => SlotStatus::Reserved, 'exhibitor_id' => $exhibitor->id, 'title' => $slotRequest->title]);
        $slotRequest->update([
            'status' => SlotRequestStatus::Accepted,
            'assigned_slot_id' => $slot->id,
            'processed_by' => $manager->id,
            'processed_at' => now(),
        ]);
        $message = $slotRequest->conferenceMessages()->create([
            'sender_id' => $manager->id,
            'body' => 'Échange conservé après annulation.',
        ]);

        $this->actingAs($manager)
            ->delete(route('responsable-conferences.requests.destroy', $slotRequest), ['reason' => 'Changement de programme'])
            ->assertRedirect();

        $this->assertSame(SlotRequestStatus::Cancelled, $slotRequest->fresh()->status);
        $this->assertSame(SlotStatus::Available, $slot->fresh()->status);
        $this->assertDatabaseHas('conference_messages', ['id' => $message->id, 'slot_request_id' => $slotRequest->id]);
        $this->assertDatabaseHas('slot_request_decisions', [
            'slot_request_id' => $slotRequest->id,
            'decision' => 'cancelled',
            'reason' => 'Changement de programme',
            'assigned_slot_id' => $slot->id,
            'processed_by' => $manager->id,
        ]);
        $this->get(route('responsable-conferences.history', ['decision' => 'cancelled']))
            ->assertOk()
            ->assertSee('Annulée')
            ->assertSee('Changement de programme');
    }

    public function test_manager_can_delete_pending_request_and_its_conversation(): void
    {
        $this->seedSlots();
        $manager = $this->manager();
        $exhibitor = $this->exhibitor('Société demandeuse');
        $slotRequest = $this->requestFor($exhibitor);
        $message = $slotRequest->conferenceMessages()->create([
            'sender_id' => $manager->id,
            'body' => 'Contexte conservé.',
        ]);

        $this->actingAs($this->commercial())
            ->delete(route('responsable-conferences.requests.destroy', $slotRequest))
            ->assertForbidden();

        $this->actingAs($manager)
            ->delete(route('responsable-conferences.requests.destroy', $slotRequest))
            ->assertRedirect();

        $this->assertDatabaseMissing('slot_requests', ['id' => $slotRequest->id]);
        $this->assertDatabaseMissing('conference_messages', ['id' => $message->id]);
        $this->assertDatabaseMissing('slot_request_decisions', ['slot_request_id' => $slotRequest->id]);
    }

    public function test_deleting_a_rejected_request_preserves_its_decision_snapshot(): void
    {
        $this->seedSlots();
        $manager = $this->manager();
        $exhibitor = $this->exhibitor('Société avec décision');
        $slotRequest = $this->requestFor($exhibitor);
        $slotRequest->update([
            'status' => SlotRequestStatus::Rejected,
            'rejection_reason' => 'Créneau indisponible',
            'processed_by' => $manager->id,
            'processed_at' => now(),
        ]);
        $decision = \App\Models\SlotRequestDecision::query()->create([
            'slot_request_id' => $slotRequest->id,
            'exhibitor_id' => $exhibitor->id,
            'exhibitor_name' => $exhibitor->company_name,
            'request_title' => $slotRequest->title,
            'request_type' => $slotRequest->type->value,
            'decision' => 'rejected',
            'reason' => 'Créneau indisponible',
            'processed_by' => $manager->id,
            'processor_name' => $manager->name,
            'processed_at' => now(),
        ]);

        $this->actingAs($manager)
            ->delete(route('responsable-conferences.requests.destroy', $slotRequest))
            ->assertRedirect();

        $this->assertDatabaseMissing('slot_requests', ['id' => $slotRequest->id]);
        $this->assertDatabaseHas('slot_request_decisions', [
            'id' => $decision->id,
            'slot_request_id' => null,
            'exhibitor_name' => $exhibitor->company_name,
            'decision' => 'rejected',
            'reason' => 'Créneau indisponible',
        ]);
    }
}
