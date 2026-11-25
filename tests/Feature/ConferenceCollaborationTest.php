<?php

namespace Tests\Feature;

use App\Enums\SlotRequestStatus;
use App\Enums\SlotStatus;
use App\Enums\UserRole;
use App\Models\ConferenceMessage;
use App\Models\Exhibitor;
use App\Models\SlotRequest;
use App\Models\User;
use App\Notifications\ConferenceCollaborationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SlotFixtures;
use Tests\TestCase;

class ConferenceCollaborationTest extends TestCase
{
    use RefreshDatabase, SlotFixtures;

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::ResponsableConferences,
            'is_active' => true,
        ]);
    }

    private function confirmedRequest(Exhibitor $exhibitor, string $slotCode = 'C2-1', array $attributes = []): SlotRequest
    {
        $slot = $this->slot($slotCode);
        $slot->update([
            'status' => SlotStatus::Reserved,
            'exhibitor_id' => $exhibitor->id,
            'commercial_id' => $exhibitor->commercial_id,
        ]);

        return $exhibitor->slotRequests()->create([
            'type' => 'conference',
            'title' => 'Présentation technique',
            'summary' => 'Résumé de la présentation.',
            'speakers' => 'Intervenante',
            'first_choice_slot_id' => $slot->id,
            'status' => SlotRequestStatus::Accepted,
            'assigned_slot_id' => $slot->id,
            ...$attributes,
        ]);
    }

    private function supportFile(string $name = 'presentation.pdf'): UploadedFile
    {
        return UploadedFile::fake()->create($name, 120, match (strtolower(pathinfo($name, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        });
    }

    public function test_exhibitor_can_submit_with_one_choice_and_cannot_open_another_exhibitors_conference(): void
    {
        Notification::fake();
        $this->seedSlots();
        $owner = $this->exhibitor('Société A');
        $other = $this->exhibitor('Société B');
        $this->manager();
        $slot = $this->slot('C2-1');

        $this->actingAs($owner->user)->post(route('exhibitor.slots.request'), [
            'type' => 'conference',
            'title' => 'Présentation',
            'summary' => 'Un résumé de cinq lignes maximum.',
            'speakers' => 'Intervenante A',
            'first_choice_slot_id' => $slot->id,
        ])->assertRedirect(route('exhibitor.slots.index'))
            ->assertSessionHasNoErrors();

        $pending = SlotRequest::query()->where('exhibitor_id', $owner->id)->firstOrFail();
        $this->assertSame(SlotRequestStatus::Pending, $pending->status);
        $this->assertNull($pending->second_choice_slot_id);

        $foreignRequest = $this->confirmedRequest($other, 'C2-2');
        $this->get(route('exhibitor.conferences.collaboration.show', $foreignRequest))
            ->assertForbidden();
        $this->get(route('exhibitor.conferences.collaboration.download', $foreignRequest))
            ->assertForbidden();
    }

    public function test_support_is_forbidden_before_confirmation_and_oversize_files_fail_validation(): void
    {
        Storage::fake('local');
        $this->seedSlots();
        $exhibitor = $this->exhibitor('Société Support');
        $pending = $exhibitor->slotRequests()->create([
            'type' => 'conference', 'title' => 'Sujet', 'summary' => 'Résumé', 'speakers' => 'Équipe',
            'first_choice_slot_id' => $this->slot('C2-1')->id, 'status' => SlotRequestStatus::Pending,
        ]);

        $this->actingAs($exhibitor->user)
            ->post(route('exhibitor.conferences.collaboration.support', $pending), [
                'support' => $this->supportFile(),
                'support_details' => 'Description détaillée suffisante.',
            ])->assertForbidden();

        $confirmed = $this->confirmedRequest($exhibitor, 'C2-2');
        $this->post(route('exhibitor.conferences.collaboration.support', $confirmed), [
            'support' => UploadedFile::fake()->create('large.pdf', 20481, 'application/pdf'),
            'support_details' => 'Description détaillée suffisante.',
        ])->assertSessionHasErrors('support');

        $this->post(route('exhibitor.conferences.collaboration.support', $confirmed), [
            'support' => UploadedFile::fake()->create('invalid.txt', 100, 'text/plain'),
            'support_details' => 'Description détaillée suffisante.',
        ])->assertSessionHasErrors('support');

        $this->post(route('exhibitor.conferences.collaboration.support', $confirmed), [
            'support' => $this->supportFile(),
            'support_details' => 'Court',
        ])->assertSessionHasErrors('support_details');

        $this->assertNull($this->slot('C2-2')->fresh()->support_path);
    }

    public function test_exhibitor_uploads_pdf_ppt_and_pptx_and_each_upload_starts_pending(): void
    {
        Storage::fake('local');
        Notification::fake();
        $this->seedSlots();
        $manager = $this->manager();
        $exhibitor = $this->exhibitor('Société Présentation');
        $request = $this->confirmedRequest($exhibitor);

        foreach (['presentation.pdf', 'presentation.ppt', 'presentation.pptx'] as $filename) {
            if ($request->support_review_status === 'pending') {
                $this->actingAs($manager)->post(route('responsable-conferences.supports.review', $request), [
                    'decision' => 'revision_requested', 'note' => 'Merci de corriger la version.',
                ])->assertRedirect();
                $request->refresh();
            }

            $this->actingAs($exhibitor->user)
                ->post(route('exhibitor.conferences.collaboration.support', $request), [
                    'support' => $this->supportFile($filename),
                    'support_details' => 'Description détaillée de cette présentation.',
                ])->assertRedirect()
                ->assertSessionHasNoErrors();

            $request->refresh();
            $this->assertSame('pending', $request->support_review_status);
            $this->assertSame('Description détaillée de cette présentation.', $request->support_details);
            Storage::disk('local')->assertExists($request->assignedSlot->fresh()->support_path);
            Notification::assertSentTo($manager, ConferenceCollaborationNotification::class, fn ($notification) => $notification->type === 'conference_support');
        }
    }

    public function test_responsable_approves_only_an_existing_pending_support_and_approved_file_cannot_be_replaced(): void
    {
        Storage::fake('local');
        Notification::fake();
        $this->seedSlots();
        $manager = $this->manager();
        $exhibitor = $this->exhibitor('Société Review');
        $request = $this->confirmedRequest($exhibitor);

        $this->actingAs($manager)
            ->post(route('responsable-conferences.supports.review', $request), ['decision' => 'approved'])
            ->assertStatus(422);
        $this->assertSame('not_submitted', $request->fresh()->support_review_status);

        $this->actingAs($exhibitor->user)
            ->post(route('exhibitor.conferences.collaboration.support', $request), [
                'support' => $this->supportFile(),
                'support_details' => 'Description détaillée de la présentation.',
            ])->assertRedirect();

        $slot = $request->assignedSlot()->firstOrFail();
        $approvedPath = $slot->support_path;
        $this->actingAs($manager)
            ->post(route('responsable-conferences.supports.review', $request), ['decision' => 'approved'])
            ->assertRedirect();
        $this->assertSame('approved', $request->fresh()->support_review_status);
        $this->actingAs($exhibitor->user)
            ->get(route('exhibitor.conferences.collaboration.download', $request))
            ->assertOk();

        $this->actingAs($exhibitor->user)
            ->post(route('exhibitor.conferences.collaboration.support', $request), [
                'support' => $this->supportFile('replacement.pdf'),
                'support_details' => 'Une autre description détaillée.',
            ])->assertForbidden();
        $this->actingAs($exhibitor->user)
            ->post(route('exhibitor.slots.support.store', $slot), ['support' => $this->supportFile('legacy.pdf')])
            ->assertRedirect(route('exhibitor.conferences.collaboration.show', $request));

        $this->assertSame($approvedPath, $slot->fresh()->support_path);
        Storage::disk('local')->assertExists($approvedPath);

        $this->actingAs($manager)
            ->post(route('responsable-conferences.supports.review', $request), [
                'decision' => 'revision_requested',
                'note' => 'Une mise à jour est nécessaire.',
            ])->assertRedirect();
        $this->assertSame('revision_requested', $request->fresh()->support_review_status);

        $this->actingAs($exhibitor->user)
            ->post(route('exhibitor.conferences.collaboration.support', $request), [
                'support' => $this->supportFile('revised.pdf'),
                'support_details' => 'Description mise à jour pour la présentation.',
            ])->assertRedirect()
            ->assertSessionHasNoErrors();
        $this->assertNotSame($approvedPath, $slot->fresh()->support_path);
    }

    public function test_revision_requires_note_and_corrected_upload_resets_review_metadata(): void
    {
        Storage::fake('local');
        Notification::fake();
        $this->seedSlots();
        $manager = $this->manager();
        $exhibitor = $this->exhibitor('Société Révision');
        $request = $this->confirmedRequest($exhibitor, attributes: [
            'support_review_status' => 'pending',
            'support_reviewed_by' => $manager->id,
            'support_reviewed_at' => now(),
        ]);
        $oldPath = $this->supportFile()->store('slot-supports', 'local');
        $request->assignedSlot->update(['support_path' => $oldPath, 'support_original_name' => 'ancienne.pdf']);

        $this->actingAs($manager)
            ->post(route('responsable-conferences.supports.review', $request), ['decision' => 'revision_requested'])
            ->assertSessionHasErrors('note');
        $this->assertSame('pending', $request->fresh()->support_review_status);

        $this->post(route('responsable-conferences.supports.review', $request), [
            'decision' => 'revision_requested', 'note' => '  Ajouter le détail du protocole.  ',
        ])->assertRedirect();
        $request->refresh();
        $this->assertSame('revision_requested', $request->support_review_status);
        $this->assertSame('Ajouter le détail du protocole.', $request->support_review_note);
        Notification::assertSentTo($exhibitor->user, ConferenceCollaborationNotification::class, fn ($notification) => $notification->type === 'conference_support_review');

        $this->actingAs($exhibitor->user)
            ->post(route('exhibitor.conferences.collaboration.support', $request), [
                'support' => $this->supportFile('corrected.pptx'),
                'support_details' => 'Description corrigée et détaillée.',
            ])->assertRedirect();

        $request->refresh();
        $request->assignedSlot->refresh();
        $this->assertSame('pending', $request->support_review_status);
        $this->assertNull($request->support_review_note);
        $this->assertNull($request->support_reviewed_at);
        $this->assertNull($request->support_reviewed_by);
        $this->assertSame('Description corrigée et détaillée.', $request->support_details);
        $this->assertNotSame($oldPath, $request->assignedSlot->support_path);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($request->assignedSlot->support_path);
    }

    public function test_commercial_and_legacy_exhibitor_endpoint_cannot_bypass_review(): void
    {
        Storage::fake('local');
        $this->seedSlots();
        $commercial = $this->commercial();
        $exhibitor = $this->exhibitor('Société Gérée', commercial: $commercial);
        $request = $this->confirmedRequest($exhibitor, attributes: ['support_review_status' => 'revision_requested']);
        $slot = $request->assignedSlot()->firstOrFail();
        $existing = $this->supportFile()->store('slot-supports', 'local');
        $slot->update(['support_path' => $existing]);

        $this->actingAs($commercial)
            ->post(route('commercial.slots.support.store', $slot), ['support' => $this->supportFile('commercial.pdf')])
            ->assertSessionHasErrors('support');

        $this->assertSame($existing, $slot->fresh()->support_path);
        $this->assertSame('revision_requested', $request->fresh()->support_review_status);
        Storage::disk('local')->assertExists($existing);
    }

    public function test_exhibitor_and_manager_messages_are_private_notified_and_read_per_conversation(): void
    {
        Notification::fake();
        $this->seedSlots();
        $manager = $this->manager();
        $exhibitor = $this->exhibitor('Société Chat');
        $first = $this->confirmedRequest($exhibitor, 'C2-1');
        $second = $this->confirmedRequest($exhibitor, 'C2-2');
        $other = $this->exhibitor('Société Autre');
        $foreign = $this->confirmedRequest($other, 'C2-3');

        $this->actingAs($exhibitor->user)
            ->post(route('exhibitor.conferences.collaboration.message', $first), ['body' => 'Bonjour, voici une question.'])
            ->assertRedirect();
        Notification::assertSentTo($manager, ConferenceCollaborationNotification::class, fn ($notification) => $notification->type === 'conference_message');

        $this->actingAs($manager)
            ->post(route('responsable-conferences.supports.message', $first), ['body' => 'Bonjour, voici la réponse.'])
            ->assertRedirect();
        Notification::assertSentTo($exhibitor->user, ConferenceCollaborationNotification::class, fn ($notification) => $notification->type === 'conference_message');

        $this->actingAs($exhibitor->user)
            ->post(route('exhibitor.conferences.collaboration.message', $first), ['body' => '   '])
            ->assertSessionHasErrors('body');
        $this->assertSame(2, ConferenceMessage::query()->count());
        $this->get(route('responsable-conferences.supports.show', $foreign))->assertForbidden();

        $this->actingAs($manager)
            ->get(route('responsable-conferences.supports.index'))
            ->assertOk()
            ->assertSee('1')
            ->assertSee(route('responsable-conferences.supports.show', $first));

        $incomingFirst = $first->conferenceMessages()->create(['sender_id' => $manager->id, 'body' => 'Message entrant 1']);
        $incomingSecond = $second->conferenceMessages()->create(['sender_id' => $manager->id, 'body' => 'Message entrant 2']);
        $outgoingFirst = $first->conferenceMessages()->create(['sender_id' => $exhibitor->user->id, 'body' => 'Message sortant']);

        $this->actingAs($exhibitor->user)
            ->get(route('exhibitor.slots.index', ['conference' => $first->id]))
            ->assertOk()
            ->assertSee('Ma conférence');
        $this->assertNotNull($incomingFirst->fresh()->read_at);
        $this->assertNull($incomingSecond->fresh()->read_at);
        $this->assertNull($outgoingFirst->fresh()->read_at);

        $managerIncoming = $first->conferenceMessages()->create(['sender_id' => $exhibitor->user->id, 'body' => 'À lire par le Responsable']);
        $this->actingAs($manager)
            ->get(route('responsable-conferences.supports.show', $first))
            ->assertOk()
            ->assertSee('Présentation technique');
        $this->assertNotNull($managerIncoming->fresh()->read_at);
    }

    public function test_collaboration_notifications_mark_read_and_open_the_owned_conversation(): void
    {
        $this->seedSlots();
        $manager = $this->manager();
        $exhibitor = $this->exhibitor('Société Notifications');
        $request = $this->confirmedRequest($exhibitor);

        $exhibitorNotification = new ConferenceCollaborationNotification(
            'conference_support_review', 'Révision', 'Une révision est demandée.', $request->id
        );
        $exhibitor->user->notify($exhibitorNotification);
        $exhibitorNotice = $exhibitor->user->notifications()->firstOrFail();

        $this->actingAs($exhibitor->user)
            ->post(route('exhibitor.notifications.open', $exhibitorNotice->id))
            ->assertRedirect(route('exhibitor.slots.index', ['conference' => $request->id]).'#conference-followup');
        $this->assertNotNull($exhibitorNotice->fresh()->read_at);

        $manager->notify(new ConferenceCollaborationNotification(
            'conference_message', 'Message', 'Nouveau message.', $request->id
        ));
        $managerNotice = $manager->notifications()->firstOrFail();

        $this->actingAs($manager)
            ->get(route('responsable-conferences.notifications.index', ['type' => 'conference_message']))
            ->assertOk()
            ->assertSee('Message');

        $this->post(route('responsable-conferences.notifications.open', $managerNotice->id))
            ->assertRedirect(route('responsable-conferences.supports.show', $request));
        $this->assertNotNull($managerNotice->fresh()->read_at);
    }
}
