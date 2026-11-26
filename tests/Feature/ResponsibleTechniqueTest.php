<?php

namespace Tests\Feature;

use App\Enums\SlotRequestStatus;
use App\Enums\SlotStatus;
use App\Enums\SpaceType;
use App\Enums\UserRole;
use App\Mail\CommercialInvitationMail;
use App\Models\Equipment;
use App\Models\Exhibitor;
use App\Models\Slot;
use App\Models\SlotRequest;
use App\Models\Space;
use App\Models\User;
use App\Services\AccountInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ResponsibleTechniqueTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_invite_a_technical_manager_but_role_cannot_create_admins(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post(route('admin.responsables-techniques.store'), [
            'name' => 'Nadia Technique',
            'email' => 'nadia.technique@example.test',
        ])->assertRedirect(route('admin.responsables-techniques.index'));

        $technician = User::query()->where('email', 'nadia.technique@example.test')->firstOrFail();
        $this->assertSame(UserRole::ResponsableTechnique, $technician->role);
        $this->assertNull($technician->password);
        $invitation = $technician->latestInvitation;
        $this->assertSame(UserRole::ResponsableTechnique->value, $invitation->role);
        Mail::assertSent(CommercialInvitationMail::class, fn (CommercialInvitationMail $mail): bool => $mail->user->is($technician)
            && $mail->role === UserRole::ResponsableTechnique->value);
        $this->get(route('admin.responsables-techniques.index'))->assertOk()->assertSee('Nadia Technique');
        $this->get(route('admin.responsables-techniques.create'))->assertOk()->assertSee('Créer un Responsable Technique');

        $issued = app(AccountInvitationService::class)->prepareResend($technician);
        $this->assertSame('recent', $issued['reason']);
        $this->assertSame(1, User::query()->where('role', UserRole::Admin)->count());
        $this->assertDatabaseHas('commercial_invitations', ['user_id' => $technician->id, 'role' => UserRole::ResponsableTechnique->value]);
    }

    public function test_technical_manager_can_view_all_read_only_sections_and_cannot_access_other_spaces_or_mutations(): void
    {
        $technician = User::factory()->create(['role' => UserRole::ResponsableTechnique]);

        $this->actingAs($technician);
        $this->get(route('responsable-technique.dashboard'))->assertOk();
        $this->get(route('responsable-technique.equipment'))->assertOk();
        $this->get(route('responsable-technique.commercials'))->assertOk();
        $this->get(route('responsable-technique.conferences'))->assertOk();
        $this->get(route('admin.index'))->assertForbidden();
        $this->get(route('commercial.index'))->assertForbidden();
        $this->get(route('responsable-conferences.dashboard'))->assertForbidden();
        $this->get(route('exposant.index'))->assertForbidden();
        $this->get(route('admin.equipements.create'))->assertForbidden();
        $this->post(route('responsable-conferences.reservations.store'), [])->assertForbidden();
    }

    public function test_technical_dashboard_and_read_only_pages_use_current_database_records(): void
    {
        $technical = User::factory()->create(['role' => UserRole::ResponsableTechnique]);
        $conferenceManager = User::factory()->create(['role' => UserRole::ResponsableConferences]);
        $commercial = User::factory()->create(['role' => UserRole::Commercial, 'is_active' => true]);
        $inactiveCommercial = User::factory()->create(['role' => UserRole::Commercial, 'is_active' => false]);
        $exhibitor = Exhibitor::query()->forceCreate([
            'commercial_id' => $commercial->id,
            'company_name' => 'Atelier Froid',
            'contact_name' => 'Sara Contact',
            'email' => 'sara@example.test',
            'stand_number' => 'A12',
        ]);
        Equipment::query()->create(['name' => 'Vitrine froide', 'category' => 'Froid', 'description' => 'Vitrine', 'price' => 0, 'quantity_available' => 7, 'is_active' => true]);
        Equipment::query()->create(['name' => 'Congélateur', 'category' => 'Froid', 'quantity_available' => 3, 'is_active' => false]);

        $space = Space::query()->create(['name' => 'Salle de conférence', 'type' => SpaceType::Conference, 'capacity' => 60]);
        $reservedSlot = Slot::query()->create([
            'space_id' => $space->id, 'code' => 'TECH-01', 'date' => '2027-02-02', 'start_time' => '09:00', 'end_time' => '10:00',
            'price_excl_tax' => 0, 'status' => SlotStatus::Reserved, 'exhibitor_id' => $exhibitor->id, 'title' => 'Réparation durable', 'speakers' => 'Intervenante A',
        ]);
        Slot::query()->create([
            'space_id' => $space->id, 'code' => 'TECH-02', 'date' => '2027-02-02', 'start_time' => '10:00', 'end_time' => '11:00',
            'price_excl_tax' => 0, 'status' => SlotStatus::Available,
        ]);
        Slot::query()->create([
            'space_id' => $space->id, 'code' => 'TECH-03', 'date' => '2027-02-02', 'start_time' => '11:00', 'end_time' => '12:00',
            'price_excl_tax' => 0, 'status' => SlotStatus::Paid, 'created_by' => $conferenceManager->id,
            'exhibitor_name' => 'Association Frigo', 'title' => 'Chaîne du froid', 'speakers' => 'Intervenant externe',
        ]);
        SlotRequest::query()->create([
            'exhibitor_id' => $exhibitor->id, 'type' => SpaceType::Conference, 'title' => 'Demande en attente',
            'summary' => 'Résumé', 'speakers' => 'Intervenante B', 'status' => SlotRequestStatus::Pending,
        ]);

        $this->actingAs($technical)
            ->get(route('responsable-technique.dashboard'))
            ->assertOk()
            ->assertSee('2')
            ->assertSee('7')
            ->assertSee('1');

        $this->get(route('responsable-technique.equipment', ['category' => 'Froid', 'status' => 'active']))
            ->assertOk()->assertSee('Vitrine froide')->assertDontSee('Congélateur');

        $this->get(route('responsable-technique.commercials'))
            ->assertOk()->assertSee($commercial->name)->assertSee('1');
        $this->get(route('responsable-technique.commercial', $commercial))
            ->assertOk()->assertSee('Atelier Froid')->assertSee('A12');

        $this->get(route('responsable-technique.conferences'))
            ->assertOk()->assertSee('Demande en attente')->assertSee('Réparation durable')->assertSee('Intervenante A')
            ->assertSee('Disponible')->assertSee('Réservé')->assertSee('Réservation directe')
            ->assertSee('Association Frigo')->assertSee('Intervenant externe')->assertDontSee($inactiveCommercial->email);

        $this->assertDatabaseHas('slots', ['id' => $reservedSlot->id, 'status' => SlotStatus::Reserved->value]);
    }

    public function test_guest_is_redirected_to_login_and_non_technical_user_cannot_view_technical_pages(): void
    {
        $this->get(route('responsable-technique.dashboard'))->assertRedirect(route('login'));
        $commercial = User::factory()->create(['role' => UserRole::Commercial]);
        $this->actingAs($commercial)->get(route('responsable-technique.dashboard'))->assertForbidden();
    }
}
