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

    private function conferenceManager(): User
    {
        return User::factory()->create([
            'role' => 'responsable_conferences',
            'is_active' => true,
        ]);
    }

    private function requestPayload(array $overrides = []): array
    {
        return array_replace([
            'type' => 'conference',
            'title' => 'Les fluides frigorigènes naturels',
            'summary' => "Ligne 1\nLigne 2\nLigne 3",
            'speakers' => 'Sara El Amrani, directrice technique',
            'first_choice_slot_id' => $this->slot('C3-1')->id,
            'second_choice_slot_id' => $this->slot('C3-2')->id,
        ], $overrides);
    }

    public function test_planning_is_reserved_to_commercials(): void
    {
        $this->seedSlots();

        $exhibitor = $this->exhibitor('ClimaTech');

        $this->get(route('commercial.slots.index'))
            ->assertRedirect(route('login'));

        $this->actingAs($exhibitor->user)
            ->get(route('commercial.slots.index'))
            ->assertForbidden();

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('commercial.slots.index'))
            ->assertForbidden();

        $this->actingAs($this->commercial())
            ->get(route('commercial.slots.index'))
            ->assertOk()
            ->assertSee('C2-1');

        $this->assertDatabaseCount('slots', 23);

        $this->assertSame(
            23,
            \App\Models\Slot::query()
                ->where(
                    'status',
                    SlotStatus::Available->value
                )
                ->count()
        );
    }

    public function test_exhibitor_request_is_accepted_and_confirmed(): void
    {
        Notification::fake();

        $this->seedSlots();

        $exhibitor = $this->exhibitor('ClimaTech');

        $this->actingAs($exhibitor->user)
            ->get(route('exhibitor.slots.index'))
            ->assertOk()
            ->assertSee('Nouvelle demande de réservation')
            ->assertSee('Choisissez vos créneaux')
            ->assertSee('Informations de la présentation')
            ->assertDontSee('Media')
            ->assertDontSee('FIRST THERM');

        $this->post(
            route('exhibitor.slots.request'),
            $this->requestPayload()
        )
            ->assertRedirect(route('exhibitor.slots.index'))
            ->assertSessionHasNoErrors();

        $request = SlotRequest::query()->firstOrFail();

        $this->assertSame(
            SlotRequestStatus::Pending,
            $request->status
        );

        $this->assertSame(
            $this->slot('C3-1')->id,
            $request->first_choice_slot_id
        );

        $this->assertSame(
            $this->slot('C3-2')->id,
            $request->second_choice_slot_id
        );

        $this->get(route('exhibitor.slots.index'))
            ->assertOk()
            ->assertViewHas(
                'requestedSlotIds',
                function ($ids): bool {
                    $expected = [
                        $this->slot('C3-1')->id,
                        $this->slot('C3-2')->id,
                    ];

                    sort($expected);

                    $actual = collect($ids)
                        ->map(fn ($id) => (int) $id)
                        ->all();

                    sort($actual);

                    return $actual === $expected;
                }
            );

        $this->actingAs($this->conferenceManager())
            ->patch(
                route(
                    'responsable-conferences.requests.accept',
                    $request
                ),
                [
                    'slot_id' => $this->slot('C3-1')->id,
                ]
            )
            ->assertSessionHasNoErrors();

        $slot = $this->slot('C3-1');

        $this->assertSame(
            SlotStatus::Reserved,
            $slot->status
        );

        $this->assertSame(
            $exhibitor->id,
            $slot->exhibitor_id
        );

        $this->assertSame(
            'Les fluides frigorigènes naturels',
            $slot->title
        );

        $request->refresh();

        $this->assertSame(
            SlotRequestStatus::Accepted,
            $request->status
        );

        $this->assertSame(
            $slot->id,
            $request->assigned_slot_id
        );

        $this->assertSame(
            SlotStatus::Available,
            $this->slot('C3-2')->status
        );

        Notification::assertSentTo(
            $exhibitor->user,
            SlotConfirmedNotification::class
        );
    }

    public function test_responsable_can_accept_second_choice(): void
    {
        Notification::fake();

        $this->seedSlots();

        $exhibitor = $this->exhibitor('ClimaTech');

        $this->actingAs($exhibitor->user)
            ->post(
                route('exhibitor.slots.request'),
                $this->requestPayload()
            )
            ->assertSessionHasNoErrors();

        $request = SlotRequest::query()->firstOrFail();

        $this->actingAs($this->conferenceManager())
            ->patch(
                route(
                    'responsable-conferences.requests.accept',
                    $request
                ),
                [
                    'slot_id' => $this->slot('C3-2')->id,
                ]
            )
            ->assertSessionHasNoErrors();

        $this->assertSame(
            SlotStatus::Reserved,
            $this->slot('C3-2')->status
        );

        $this->assertSame(
            SlotStatus::Available,
            $this->slot('C3-1')->status
        );

        $this->assertSame(
            $this->slot('C3-2')->id,
            $request->fresh()->assigned_slot_id
        );

        Notification::assertSentTo(
            $exhibitor->user,
            SlotConfirmedNotification::class
        );
    }

    public function test_second_choice_is_optional_but_must_be_different(): void
    {
        Notification::fake();

        $this->seedSlots();

        $exhibitor = $this->exhibitor('ClimaTech');

        $payload = $this->requestPayload();

        unset($payload['second_choice_slot_id']);

        // Un seul créneau doit suffire.
        $this->actingAs($exhibitor->user)
            ->post(
                route('exhibitor.slots.request'),
                $payload
            )
            ->assertRedirect(route('exhibitor.slots.index'))
            ->assertSessionHasNoErrors();

        $request = SlotRequest::query()->firstOrFail();

        $this->assertSame(
            $this->slot('C3-1')->id,
            $request->first_choice_slot_id
        );

        $this->assertNull(
            $request->second_choice_slot_id
        );

        $this->assertSame(
            SlotRequestStatus::Pending,
            $request->status
        );

        // Le deuxième choix ne peut pas être
        // identique au premier choix.
        $this->post(
            route('exhibitor.slots.request'),
            $this->requestPayload([
                'first_choice_slot_id' => $this->slot('C3-3')->id,
                'second_choice_slot_id' => $this->slot('C3-3')->id,
            ])
        )->assertSessionHasErrors('second_choice_slot_id');

        $this->assertDatabaseCount('slot_requests', 1);
    }

    public function test_request_with_one_choice_can_be_accepted(): void
    {
        Notification::fake();

        $this->seedSlots();

        $exhibitor = $this->exhibitor('ClimaTech');

        $payload = $this->requestPayload([
            'first_choice_slot_id' => $this->slot('C3-4')->id,
        ]);

        unset($payload['second_choice_slot_id']);

        $this->actingAs($exhibitor->user)
            ->post(
                route('exhibitor.slots.request'),
                $payload
            )
            ->assertSessionHasNoErrors();

        $request = SlotRequest::query()->firstOrFail();

        $manager = $this->conferenceManager();

        $this->actingAs($manager)
            ->patch(
                route(
                    'responsable-conferences.requests.accept',
                    $request
                ),
                [
                    'slot_id' => $this->slot('C3-4')->id,
                ]
            )
            ->assertSessionHasNoErrors();

        $this->assertSame(
            SlotRequestStatus::Accepted,
            $request->fresh()->status
        );

        $this->assertSame(
            $this->slot('C3-4')->id,
            $request->fresh()->assigned_slot_id
        );

        $this->assertSame(
            SlotStatus::Reserved,
            $this->slot('C3-4')->status
        );
    }

    public function test_choices_must_have_same_space(): void
    {
        $this->seedSlots();

        $exhibitor = $this->exhibitor('ClimaTech');

        $this->actingAs($exhibitor->user)
            ->post(
                route('exhibitor.slots.request'),
                $this->requestPayload([
                    'second_choice_slot_id' => $this->slot('L3-1')->id,
                ])
            )
            ->assertSessionHasErrors('second_choice_slot_id');

        $this->assertDatabaseCount('slot_requests', 0);
    }

    public function test_summary_is_limited_to_five_lines(): void
    {
        $this->seedSlots();

        $exhibitor = $this->exhibitor('ClimaTech');

        $this->actingAs($exhibitor->user)
            ->post(
                route('exhibitor.slots.request'),
                $this->requestPayload([
                    'summary' => "1\n2\n3\n4\n5\n6",
                ])
            )
            ->assertSessionHasErrors('summary');
    }

    public function test_request_can_be_rejected_with_a_reason(): void
    {
        Notification::fake();

        $this->seedSlots();

        $exhibitor = $this->exhibitor('ClimaTech');

        $request = $exhibitor->slotRequests()->create(
            $this->requestPayload() + [
                'status' => SlotRequestStatus::Pending,
            ]
        );

        $manager = $this->conferenceManager();

        $this->actingAs($manager);

        $this->patch(
            route(
                'responsable-conferences.requests.reject',
                $request
            ),
            []
        )->assertSessionHasErrors('rejection_reason');

        $this->patch(
            route(
                'responsable-conferences.requests.reject',
                $request
            ),
            [
                'rejection_reason' => 'Sujet hors thématique',
            ]
        )->assertSessionHasNoErrors();

        $this->assertSame(
            SlotRequestStatus::Rejected,
            $request->fresh()->status
        );

        $this->assertDatabaseHas(
            'slot_request_decisions',
            [
                'slot_request_id' => $request->id,
                'decision' => 'rejected',
                'processed_by' => $manager->id,
            ]
        );

        $this->assertSame(
            $request->fresh()->rejection_reason,
            \App\Models\SlotRequestDecision::query()
                ->firstOrFail()->reason
        );

        Notification::assertSentTo(
            $exhibitor->user,
            SlotRequestRejectedNotification::class
        );

        $this->patch(
            route(
                'responsable-conferences.requests.accept',
                $request
            ),
            [
                'slot_id' => $this->slot('C3-1')->id,
            ]
        )->assertForbidden();
    }

    public function test_assigning_a_slot_from_planning_notifies_exhibitor(): void
    {
        Notification::fake();

        $this->seedSlots();

        $commercial = $this->commercial();

        $exhibitor = $this->exhibitor(
            'ClimaTech',
            commercial: $commercial
        );

        $manager = User::factory()->create(['role' => 'responsable_conferences', 'is_active' => true]);

        $this->actingAs($manager)
            ->post(
                route('responsable-conferences.reservations.store'),
                [
                    'status' => 'reserved',
                    'holder_type' => 'exhibitor',
                    'exhibitor_id' => $exhibitor->id,
                    'slot_id' => $this->slot('L2-1')->id,
                    'space_id' => $this->slot('L2-1')->space_id,
                    'date' => $this->slot('L2-1')->date->toDateString(),
                ]
            )
            ->assertSessionHasNoErrors();

        $this->assertSame(
            SlotStatus::Reserved,
            $this->slot('L2-1')->status
        );

        Notification::assertSentTo(
            $exhibitor->user,
            SlotConfirmedNotification::class
        );
    }

    public function test_support_upload_is_limited_to_owner_of_confirmed_slot(): void
    {
        Storage::fake('local');

        $this->seedSlots();

        $commercial = $this->commercial();

        $owner = $this->exhibitor(
            'ClimaTech',
            commercial: $commercial
        );

        $other = $this->exhibitor('FroidPlus');

        $slot = $this->slot('C2-1');

        $slot->update([
            'status' => SlotStatus::Option,
            'exhibitor_id' => $owner->id,
        ]);

        $pdf = fn () => UploadedFile::fake()->create(
            'presentation.pdf',
            500,
            'application/pdf'
        );

        // Option non confirmée.
        $this->actingAs($owner->user)
            ->post(
                route('exhibitor.slots.support.store', $slot),
                ['support' => $pdf()]
            )
            ->assertForbidden();

        $slot->update([
            'status' => SlotStatus::Reserved,
        ]);

        // Autre exposant : accès interdit.
        $this->actingAs($other->user)
            ->post(
                route('exhibitor.slots.support.store', $slot),
                ['support' => $pdf()]
            )
            ->assertForbidden();

        // Fichier invalide.
        $this->actingAs($owner->user)
            ->post(
                route('exhibitor.slots.support.store', $slot),
                [
                    'support' => UploadedFile::fake()->create(
                        'notes.docx',
                        100
                    ),
                ]
            )
            ->assertSessionHasErrors('support');

        // Taille supérieure à 20 Mo.
        $this->post(
            route('exhibitor.slots.support.store', $slot),
            [
                'support' => UploadedFile::fake()->create(
                    'trop-lourd.pdf',
                    21000,
                    'application/pdf'
                ),
            ]
        )->assertSessionHasErrors('support');

        // Fichier valide.
        $this->post(
            route('exhibitor.slots.support.store', $slot),
            ['support' => $pdf()]
        )->assertSessionHasNoErrors();

        $slot->refresh();

        $this->assertNotNull($slot->support_path);

        $this->assertSame(
            'presentation.pdf',
            $slot->support_original_name
        );

        Storage::disk('local')
            ->assertExists($slot->support_path);

        $this->get(
            route('exhibitor.slots.support.download', $slot)
        )->assertOk();

        $this->actingAs($other->user)
            ->get(
                route('exhibitor.slots.support.download', $slot)
            )
            ->assertForbidden();

        $this->actingAs($commercial)
            ->get(
                route('commercial.slots.support', $slot)
            )
            ->assertOk();
    }

    public function test_reminders_are_sent_once_at_event_deadline(): void
    {
        Notification::fake();

        $this->seedSlots();

        $exhibitor = $this->exhibitor('ClimaTech');

        $slot = $this->slot('C2-1');

        $slot->update([
            'status' => SlotStatus::Reserved,
            'exhibitor_id' => $exhibitor->id,
        ]);

        // J-8 avant ouverture.
        $this->travelTo(
            \Illuminate\Support\Carbon::parse(
                '2027-01-25 09:00'
            )
        );

        $this->artisan('slots:send-reminders')
            ->assertSuccessful();

        Notification::assertNothingSentTo(
            $exhibitor->user
        );

        // J-7 avant ouverture.
        $this->travelTo(
            \Illuminate\Support\Carbon::parse(
                '2027-01-26 09:00'
            )
        );

        $this->artisan('slots:send-reminders')
            ->assertSuccessful();

        $this->artisan('slots:send-reminders')
            ->assertSuccessful();

        Notification::assertSentToTimes(
            $exhibitor->user,
            SlotSupportReminderNotification::class,
            1
        );

        // La veille de la conférence.
        $this->travelTo(
            \Illuminate\Support\Carbon::parse(
                '2027-02-02 09:00'
            )
        );

        $this->artisan('slots:send-reminders')
            ->assertSuccessful();

        $this->artisan('slots:send-reminders')
            ->assertSuccessful();

        Notification::assertSentToTimes(
            $exhibitor->user,
            SlotEveReminderNotification::class,
            1
        );

        $this->travelBack();
    }

    public function test_csv_and_program_exports(): void
    {
        $this->seedSlots();

        $commercial = $this->commercial();

        $this->slot('C2-2')->update([
            'status' => SlotStatus::Reserved,
            'exhibitor_id' => null,
            'exhibitor_name' => 'Societe Test',
            'commercial_id' => $commercial->id,
        ]);

        $this->actingAs($commercial);

        $csv = $this->get(
            route('commercial.slots.export.csv')
        )->assertOk();

        $content = $csv->streamedContent();

        $this->assertStringContainsString(
            'C2-2;"Salle de conférence";03/02/2027;10:30;11:30;1500,00;Réservé;"Societe Test"',
            $content
        );

        $this->assertStringContainsString(
            'C2-1;"Salle de conférence";03/02/2027;09:30;10:30;1500,00;Disponible',
            $content
        );

        foreach (['fr', 'en'] as $locale) {
            $response = $this->get(
                route('commercial.slots.export.pdf', $locale)
            )->assertOk();

            if (class_exists(
                \Barryvdh\DomPDF\Facade\Pdf::class
            )) {
                $response->assertHeader(
                    'content-type',
                    'application/pdf'
                );
            } else {
                $response->assertSee('Societe Test');
            }
        }

        $this->assertSame(
            SlotStatus::Available,
            $this->slot('C2-1')->status
        );
    }
}
