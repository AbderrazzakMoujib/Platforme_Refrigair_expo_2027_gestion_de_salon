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
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_sees_who_booked_each_slot_and_the_revenue(): void
    {
        $this->seedSlots();

        $commercial = $this->commercial();

        $exhibitor = $this->exhibitor(
            'ClimaTech',
            commercial: $commercial
        );

        // Réservation réelle créée uniquement pour ce test.
        $this->slot('C3-1')->update([
            'status' => SlotStatus::Paid,
            'exhibitor_id' => $exhibitor->id,
            'commercial_id' => $commercial->id,
            'title' => 'Froid durable',
        ]);

        // Réservation externe, sans compte exposant.
        $this->slot('L1-1')->update([
            'status' => SlotStatus::Reserved,
            'exhibitor_name' => 'Entreprise Test',
            'commercial_id' => $commercial->id,
            'title' => 'Innovations HVAC-R',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.slots.index'))
            ->assertOk()

            // 1 500 € conférence + 1 000 € lounge.
            ->assertViewHas(
                'stats',
                fn (array $stats) =>
                    $stats['total']['revenue'] === 2500.0
                    && $stats['total']['collected'] === 1500.0
                    && $stats['total']['sold'] === 2
            )

            // Vérification des titulaires.
            ->assertViewHas(
                'holders',
                function ($holders): bool {
                    $climaTech = $holders->firstWhere(
                        'name',
                        'ClimaTech'
                    );

                    $external = $holders->firstWhere(
                        'name',
                        'Entreprise Test'
                    );

                    return $climaTech !== null
                        && $external !== null
                        && $external['codes'] === ['L1-1']
                        && $external['lounge'] === 1
                        && $external['revenue'] === 1000.0;
                }
            )

->assertSee('Répartition par société')
->assertSee('ClimaTech')
->assertSee('Entreprise Test')
->assertSee('Froid durable')
->assertSee('CA réservé HT')
->assertSee('2 500 €');
    }

    public function test_admin_can_filter_slots(): void
    {
        $this->seedSlots();

        // Créneau réservé à une société externe.
        $this->slot('C2-2')->update([
            'status' => SlotStatus::Reserved,
            'exhibitor_name' => 'Media',
        ]);

        $this->actingAs($this->admin());

        // Recherche par nom du titulaire.
        $this->get(
            route('admin.slots.index', [
                'search' => 'media',
            ])
        )
            ->assertOk()
            ->assertViewHas(
                'slots',
                fn ($slots) =>
                    $slots->pluck('code')->all() === ['C2-2']
            );

        // 12 conférences - 1 réservée = 11 disponibles.
        $this->get(
            route('admin.slots.index', [
                'space' => 'conference',
                'status' => 'available',
            ])
        )
            ->assertOk()
            ->assertViewHas(
                'slots',
                fn ($slots) => $slots->count() === 11
            );

        // Le 2 février contient 3 créneaux Lounge.
        $this->get(
            route('admin.slots.index', [
                'date' => '2027-02-02',
            ])
        )
            ->assertOk()
            ->assertViewHas(
                'slots',
                fn ($slots) =>
                    $slots->pluck('code')->all() === [
                        'L1-1',
                        'L1-2',
                        'L1-3',
                    ]
            );

        // Un seul créneau réservé dans ce scénario.
        $this->get(
            route('admin.slots.index', [
                'status' => 'taken',
            ])
        )
            ->assertOk()
            ->assertViewHas(
                'slots',
                fn ($slots) => $slots->count() === 1
            );
    }

    public function test_admin_can_export_and_download_supports_but_not_modify(): void
    {
        Storage::fake('local');

        $this->seedSlots();

        $exhibitor = $this->exhibitor('ClimaTech');

        $slot = $this->slot('C2-1');

        $slot->update([
            'status' => SlotStatus::Reserved,
            'exhibitor_id' => $exhibitor->id,
        ]);

        // L'exposant envoie un support.
        $this->actingAs($exhibitor->user)
            ->post(
                route(
                    'exhibitor.slots.support.store',
                    $slot
                ),
                [
                    'support' => UploadedFile::fake()
                        ->create(
                            'deck.pdf',
                            200,
                            'application/pdf'
                        ),
                ]
            )
            ->assertSessionHasNoErrors();

        // L'admin peut consulter et exporter.
        $this->actingAs($this->admin());

        $this->get(
            route('admin.slots.export.csv')
        )->assertOk();

        $this->get(
            route('admin.slots.export.pdf', 'en')
        )->assertOk();

        $this->get(
            route('admin.slots.support', $slot)
        )->assertOk();

        // L'admin ne peut pas modifier une réservation
        // via les routes réservées au Commercial.
        $this->put(
            route('commercial.slots.update', $slot),
            [
                'status' => 'available',
            ]
        )->assertForbidden();

        $this->assertSame(
            SlotStatus::Reserved,
            $slot->fresh()->status
        );
    }

    public function test_admin_page_is_forbidden_to_other_roles(): void
    {
        $this->seedSlots();

        // Visiteur non connecté.
        $this->get(
            route('admin.slots.index')
        )->assertRedirect(route('login'));

        // Commercial.
        $this->actingAs($this->commercial())
            ->get(route('admin.slots.index'))
            ->assertForbidden();

        // Exposant.
        $exhibitor = $this->exhibitor('ClimaTech');

        $this->actingAs($exhibitor->user)
            ->get(route('admin.slots.index'))
            ->assertForbidden();
    }

    public function test_opening_the_request_form_url_redirects_the_exhibitor(): void
    {
        $exhibitor = $this->exhibitor('ClimaTech');

        $this->actingAs($exhibitor->user)
            ->get('/exposant/conferences/demandes')
            ->assertRedirect(
                route('exhibitor.slots.index')
            );
    }
}
