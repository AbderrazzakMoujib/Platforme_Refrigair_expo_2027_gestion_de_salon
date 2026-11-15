<?php

namespace Tests\Feature;

use App\Models\Salon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSalonsTest extends TestCase
{
    use RefreshDatabase;

    private function salonData(): array
    {
        return [
            'name' => 'Refrigair Expo',
            'edition' => '2027',
            'city' => 'Casablanca',
            'venue' => 'OFEC Casablanca',
            'start_date' => '2027-02-02',
            'end_date' => '2027-02-04',
            'description' => 'Salon professionnel du froid et de la climatisation.',
            'status' => 'planned',
            'registration_open' => '0',
        ];
    }

    public function test_admin_can_create_view_and_update_salon(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.salons.index'))
            ->assertOk()
            ->assertSee('Gestion des salons');

        $this->get(route('admin.salons.create'))
            ->assertOk()
            ->assertSee('Créer un salon');

        $this->post(
            route('admin.salons.store'),
            $this->salonData()
        )->assertRedirect();

        $salon = Salon::firstOrFail();

        $this->assertDatabaseHas('salons', [
            'id' => $salon->id,
            'name' => 'Refrigair Expo',
            'edition' => '2027',
            'city' => 'Casablanca',
            'status' => 'planned',
            'registration_open' => 0,
        ]);

        $this->get(route('admin.salons.show', $salon))
            ->assertOk()
            ->assertSee('OFEC Casablanca');

        $this->put(
            route('admin.salons.update', $salon),
            [
                ...$this->salonData(),
                'venue' => 'OFEC - Casablanca',
                'status' => 'active',
                'registration_open' => '1',
            ]
        )->assertRedirect(route('admin.salons.show', $salon));

        $this->assertDatabaseHas('salons', [
            'id' => $salon->id,
            'venue' => 'OFEC - Casablanca',
            'status' => 'active',
            'registration_open' => 1,
        ]);
    }

    public function test_admin_can_filter_salons(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Salon::create($this->salonData());

        Salon::create([
            ...$this->salonData(),
            'name' => 'Autre Salon',
            'edition' => '2028',
            'city' => 'Rabat',
            'status' => 'completed',
            'registration_open' => '0',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.salons.index', [
                'search' => 'Refrigair',
                'status' => 'planned',
            ]))
            ->assertOk()
            ->assertSee('Refrigair Expo')
            ->assertDontSee('Autre Salon');
    }

    public function test_admin_can_toggle_salon_registrations(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $salon = Salon::create($this->salonData());

        $this->actingAs($admin)
            ->patch(route('admin.salons.registration', $salon))
            ->assertRedirect();

        $this->assertTrue($salon->fresh()->registration_open);

        $this->patch(route('admin.salons.registration', $salon))
            ->assertRedirect();

        $this->assertFalse($salon->fresh()->registration_open);
    }

    public function test_invalid_dates_and_closed_status_rules(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.salons.store'), [
                ...$this->salonData(),
                'start_date' => '2027-02-05',
                'end_date' => '2027-02-02',
            ])
            ->assertSessionHasErrors('end_date');

        $this->post(route('admin.salons.store'), [
            ...$this->salonData(),
            'status' => 'completed',
            'registration_open' => '1',
        ])
            ->assertSessionHasErrors('registration_open');

        $this->assertDatabaseCount('salons', 0);
    }

    public function test_completed_salon_cannot_reopen_registrations(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $salon = Salon::create([
            ...$this->salonData(),
            'status' => 'completed',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.salons.registration', $salon))
            ->assertSessionHasErrors('registration');

        $this->assertFalse($salon->fresh()->registration_open);
    }

    public function test_commercial_cannot_access_admin_salons(): void
    {
        $commercial = User::factory()->create([
            'role' => 'commercial',
        ]);

        $this->actingAs($commercial)
            ->get(route('admin.salons.index'))
            ->assertForbidden();

        $this->post(
            route('admin.salons.store'),
            $this->salonData()
        )->assertForbidden();

        $this->assertDatabaseCount('salons', 0);
    }
}
