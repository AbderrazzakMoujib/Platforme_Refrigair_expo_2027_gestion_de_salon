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
use Illuminate\Support\Facades\Storage;
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
            ->assertOk()->assertSee('Réparation durable')->assertSee('Disponible')->assertSee('Réservé')
            ->assertDontSee('Demande en attente')->assertDontSee($inactiveCommercial->email);
        $this->get(route('responsable-technique.conferences', ['tab' => 'requests']))
            ->assertOk()->assertSee('Demande en attente')->assertSee('Intervenante B');
        $this->get(route('responsable-technique.conferences', ['tab' => 'reservations']))
            ->assertOk()->assertSee('Association Frigo')->assertSee('Intervenant externe')->assertSee('Réservation directe');

        $this->assertDatabaseHas('slots', ['id' => $reservedSlot->id, 'status' => SlotStatus::Reserved->value]);
    }

    public function test_guest_is_redirected_to_login_and_non_technical_user_cannot_view_technical_pages(): void
    {
        $this->get(route('responsable-technique.dashboard'))->assertRedirect(route('login'));
        $commercial = User::factory()->create(['role' => UserRole::Commercial]);
        $this->actingAs($commercial)->get(route('responsable-technique.dashboard'))->assertForbidden();
    }

    public function test_dashboard_shows_dynamic_category_breakdown_recent_equipment_and_direct_bookings(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('equipment/refrigair/equipment-7.png', 'test image');

        $technical = User::factory()->create(['role' => UserRole::ResponsableTechnique]);
        $manager = User::factory()->create(['role' => UserRole::ResponsableConferences]);

        foreach (range(1, 7) as $index) {
            Equipment::query()->create([
                'name' => "Équipement {$index}",
                'category' => $index <= 5 ? 'Froid' : 'Cuisine',
                'quantity_available' => $index,
                'is_active' => $index !== 2,
                'price' => 0,
                'image_path' => match ($index) {
                    6 => 'equipment/refrigair/missing.png',
                    7 => 'equipment/refrigair/equipment-7.png',
                    default => null,
                },
                'created_at' => now()->addMinutes($index),
                'updated_at' => now()->addMinutes($index),
            ]);
        }

        $space = Space::query()->create(['name' => 'Salle de conférence', 'type' => SpaceType::Conference, 'capacity' => 60]);
        Slot::query()->create([
            'space_id' => $space->id,
            'code' => 'DIRECT-TECH',
            'date' => '2027-02-02',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'price_excl_tax' => 0,
            'status' => SlotStatus::Reserved,
            'created_by' => $manager->id,
            'exhibitor_name' => 'Société invitée',
            'title' => 'Réservation directe',
        ]);

        $this->actingAs($technical)->get(route('responsable-technique.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats): bool => $stats['equipment_total'] === 7
                && $stats['equipment_active'] === 6
                && $stats['available_quantity'] === 26
                && $stats['active_commercials'] === 0
                && $stats['exhibitors'] === 0
                && $stats['confirmed_reservations'] === 1
                && $stats['pending_requests'] === 0
                && $stats['available_slots'] === 0
                && $stats['direct_reservations'] === 1)
            ->assertViewHas('equipmentCategories', fn ($categories): bool => $categories->firstWhere('label', 'Froid')['total'] === 5
                && $categories->firstWhere('label', 'Cuisine')['total'] === 2)
            ->assertSee('Équipement 7')
            ->assertDontSee('Équipement 1')
            ->assertSee('Équipements par catégorie')
            ->assertSee('Activité commerciale')
            ->assertSee('Conférences & Lounge')
            ->assertSee('Derniers équipements du catalogue')
            ->assertSee('2–4 février 2027')
            ->assertSee('Réservations directes')
            ->assertSeeInOrder([
                'Équipements au catalogue', 'Équipements actifs', 'Commerciaux actifs', 'Exposants inscrits',
                'Réservations confirmées', 'Demandes en attente', 'Créneaux disponibles', 'Quantité disponible',
            ])
            ->assertSee('equipment/refrigair/equipment-7.png')
            ->assertSee('Aucune image disponible');
    }

    public function test_equipment_page_filters_sorts_switches_view_and_uses_requested_page_size(): void
    {
        $technical = User::factory()->create(['role' => UserRole::ResponsableTechnique]);
        Equipment::query()->create(['name' => 'Unité compacte', 'category' => 'Froid', 'description' => 'Compresseur', 'price' => 100, 'quantity_available' => 4, 'is_active' => true]);
        Equipment::query()->create(['name' => 'Vitrine positive', 'category' => 'Froid', 'description' => 'Réfrigération', 'price' => 250, 'quantity_available' => 2, 'is_active' => true]);
        Equipment::query()->create(['name' => 'Table inox', 'category' => 'Mobilier', 'description' => 'Préparation', 'price' => 50, 'quantity_available' => 9, 'is_active' => false]);

        $this->actingAs($technical)->get(route('responsable-technique.equipment', [
            'search' => 'Froid',
            'category' => 'Froid',
            'status' => 'active',
            'sort' => 'price_desc',
            'view' => 'list',
            'per_page' => 6,
        ]))->assertOk()
            ->assertViewHas('equipment', fn ($equipment): bool => $equipment->total() === 2
                && $equipment->perPage() === 6
                && $equipment->first()->name === 'Vitrine positive')
            ->assertViewHas('stats', fn (array $stats): bool => $stats['total'] === 3
                && $stats['active'] === 2
                && $stats['quantity'] === 15
                && $stats['categories'] === 2)
            ->assertSee('Trier par')
            ->assertSee('Vitrine positive')
            ->assertDontSee('Table inox')
            ->assertSee('Éléments par page');
    }

    public function test_commercial_list_uses_real_statistics_and_opens_a_selected_read_only_panel(): void
    {
        $technical = User::factory()->create(['role' => UserRole::ResponsableTechnique]);
        $commercial = User::factory()->create([
            'role' => UserRole::Commercial,
            'name' => 'Nadia Commercial',
            'phone' => '+212600000001',
            'is_active' => true,
        ]);
        $inactiveCommercial = User::factory()->create(['role' => UserRole::Commercial, 'is_active' => false]);
        $exhibitorAccount = User::factory()->create(['role' => UserRole::Exposant, 'is_active' => true]);

        Exhibitor::query()->forceCreate([
            'commercial_id' => $commercial->id,
            'user_id' => $exhibitorAccount->id,
            'company_name' => 'Atelier Thermique',
            'contact_name' => 'Amal Contact',
            'email' => 'amal@example.test',
            'stand_number' => 'B12',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);
        Exhibitor::query()->forceCreate([
            'commercial_id' => $commercial->id,
            'company_name' => 'Froid Moderne',
            'contact_name' => 'Rachid Contact',
            'email' => 'rachid@example.test',
            'stand_number' => null,
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ]);
        Exhibitor::query()->forceCreate([
            'commercial_id' => $inactiveCommercial->id,
            'company_name' => 'Ancienne Société',
            'contact_name' => 'Contact Ancien',
            'email' => 'ancien@example.test',
            'created_at' => now()->subDays(45),
            'updated_at' => now()->subDays(45),
        ]);

        $this->actingAs($technical)->get(route('responsable-technique.commercials', ['selected' => $commercial->id]))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats): bool => $stats['active'] === 1
                && $stats['exhibitors'] === 3
                && $stats['average_exhibitors'] === 1.5
                && $stats['new_exhibitors_30_days'] === 2)
            ->assertViewHas('selectedExhibitors', fn ($exhibitors): bool => $exhibitors->total() === 2)
            ->assertSee('Nadia Commercial')
            ->assertSee('+212600000001')
            ->assertSee('Atelier Thermique')
            ->assertSee('Froid Moderne')
            ->assertSee('B12')
            ->assertSee('Activité');

        $this->get(route('responsable-technique.commercials', ['selected' => $commercial->id, 'tab' => 'activity']))
            ->assertOk()
            ->assertSee('Compte créé')
            ->assertSee('Exposants créés sur 30 jours');

        $emptyCommercial = User::factory()->create(['role' => UserRole::Commercial, 'is_active' => true]);
        $this->get(route('responsable-technique.commercials', ['selected' => $emptyCommercial->id]))
            ->assertOk()
            ->assertSee('Aucun exposant associé')
            ->assertSee('Ce commercial n’a pas encore d’exposant.');

        $this->get(route('responsable-technique.commercial', $commercial))
            ->assertOk()
            ->assertSee('Activité disponible')
            ->assertSee('Atelier Thermique');
    }

    public function test_commercial_search_status_filter_pagination_and_read_only_routes(): void
    {
        $technical = User::factory()->create(['role' => UserRole::ResponsableTechnique]);
        foreach (range(1, 11) as $index) {
            User::factory()->create([
                'role' => UserRole::Commercial,
                'name' => sprintf('Commercial %02d', $index),
                'email' => sprintf('commercial%02d@example.test', $index),
                'is_active' => $index !== 11,
            ]);
        }

        $this->actingAs($technical)->get(route('responsable-technique.commercials', ['search' => 'Commercial 11', 'status' => 'inactive']))
            ->assertOk()
            ->assertSee('Commercial 11')
            ->assertDontSee('Commercial 01');

        $this->get(route('responsable-technique.commercials', ['page' => 2]))
            ->assertOk()
            ->assertViewHas('commercials', fn ($commercials): bool => $commercials->total() === 11
                && $commercials->currentPage() === 2)
            ->assertSee('Commercial 11');

        $this->put('/responsable-technique/commerciaux/1', ['name' => 'Intrusion'])->assertStatus(405);
        $this->assertDatabaseMissing('users', ['name' => 'Intrusion']);
    }

    public function test_conference_calendar_groups_real_days_and_spaces_and_opens_read_only_slot_details(): void
    {
        $technical = User::factory()->create(['role' => UserRole::ResponsableTechnique]);
        $manager = User::factory()->create(['role' => UserRole::ResponsableConferences]);
        $commercial = User::factory()->create(['role' => UserRole::Commercial]);
        $exhibitor = Exhibitor::query()->forceCreate([
            'commercial_id' => $commercial->id,
            'company_name' => 'Thermique Atlas',
            'contact_name' => 'Salma Contact',
            'email' => 'salma@example.test',
        ]);
        $conferenceRoom = Space::query()->create(['name' => 'Salle Conférence A', 'type' => SpaceType::Conference, 'capacity' => 60]);
        $lounge = Space::query()->create(['name' => 'Lounge des Technologies', 'type' => SpaceType::Lounge, 'capacity' => 30]);

        $freeSlot = Slot::query()->create([
            'space_id' => $conferenceRoom->id, 'code' => 'CAL-01', 'date' => '2027-02-02', 'start_time' => '09:00', 'end_time' => '10:00',
            'price_excl_tax' => 0, 'status' => SlotStatus::Available,
        ]);
        $acceptedSlot = Slot::query()->create([
            'space_id' => $lounge->id, 'code' => 'CAL-02', 'date' => '2027-02-02', 'start_time' => '09:30', 'end_time' => '11:00',
            'price_excl_tax' => 0, 'status' => SlotStatus::Reserved, 'exhibitor_id' => $exhibitor->id, 'commercial_id' => $commercial->id,
            'title' => 'Réfrigération durable', 'speakers' => 'Intervenante externe',
        ]);
        $directSlot = Slot::query()->create([
            'space_id' => $conferenceRoom->id, 'code' => 'CAL-03', 'date' => '2027-02-03', 'start_time' => '11:00', 'end_time' => '12:30',
            'price_excl_tax' => 0, 'status' => SlotStatus::Paid, 'created_by' => $manager->id,
            'exhibitor_name' => 'Association Froid', 'title' => 'Chaîne du froid',
        ]);
        SlotRequest::query()->create([
            'exhibitor_id' => $exhibitor->id, 'type' => SpaceType::Lounge, 'title' => 'Réfrigération durable',
            'summary' => 'Présentation technique', 'speakers' => 'Intervenante externe', 'status' => SlotRequestStatus::Accepted,
            'first_choice_slot_id' => $acceptedSlot->id, 'assigned_slot_id' => $acceptedSlot->id,
            'processed_by' => $manager->id, 'processed_at' => now(),
        ]);
        SlotRequest::query()->create([
            'exhibitor_id' => $exhibitor->id, 'type' => SpaceType::Conference, 'title' => 'Demande en attente',
            'summary' => 'Résumé', 'speakers' => 'Intervenant B', 'status' => SlotRequestStatus::Pending,
            'first_choice_slot_id' => $freeSlot->id,
        ]);

        $this->actingAs($technical)->get(route('responsable-technique.conferences', ['mode' => 'week']))
            ->assertOk()
            ->assertViewHas('daySchedules', fn ($days): bool => $days->count() === 2
                && $days->first()['slots']->count() === 2)
            ->assertSee('Salle Conférence A')
            ->assertSee('Lounge des Technologies')
            ->assertSee('Réfrigération durable')
            ->assertSee('Réfrigération durable');

        $this->get(route('responsable-technique.conferences', [
            'mode' => 'day', 'date' => '2027-02-02', 'selected' => $acceptedSlot->id,
        ]))->assertOk()
            ->assertViewHas('selectedSlot', fn ($slot): bool => $slot?->is($acceptedSlot))
            ->assertSee('Détail du créneau')
            ->assertSee('Thermique Atlas')
            ->assertSee('Intervenante externe')
            ->assertSee('Demande exposant acceptée')
            ->assertSee('Commercial associé')
            ->assertSee('1 h 30 min');

        $this->get(route('responsable-technique.conferences', ['tab' => 'available', 'date' => '2027-02-02']))
            ->assertOk()->assertSee('CAL-01')->assertDontSee('CAL-02')->assertDontSee('CAL-03');
        $this->get(route('responsable-technique.conferences', ['tab' => 'reservations', 'mode' => 'week']))
            ->assertOk()->assertSee('Réfrigération durable')->assertSee('Chaîne du froid')->assertDontSee('CAL-01');
        $this->get(route('responsable-technique.conferences', ['tab' => 'requests', 'request_status' => 'accepted']))
            ->assertOk()->assertSee('Réfrigération durable')->assertDontSee('Demande en attente');
        $this->put('/responsable-technique/conferences/'.$freeSlot->id, ['status' => 'paid'])->assertNotFound();
        $this->assertDatabaseHas('slots', ['id' => $directSlot->id, 'status' => SlotStatus::Paid->value]);
    }
}
