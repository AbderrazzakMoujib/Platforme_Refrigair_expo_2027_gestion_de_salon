<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedEquipmentCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function createEquipment(?int $commercialId): Equipment
    {
        return Equipment::create([
            'name' => 'Chaise de conférence',
            'category' => 'Mobilier',
            'description' => 'Chaise pour les exposants',
            'price' => 150,
            'quantity_available' => 20,
            'is_active' => true,
            'commercial_id' => $commercialId,
        ]);
    }

    public function test_commercials_can_see_shared_equipment(): void
    {
        $commercialA = User::factory()->create([
            'role' => 'commercial',
        ]);

        $commercialB = User::factory()->create([
            'role' => 'commercial',
        ]);

        $this->createEquipment($commercialA->id);

        $this->actingAs($commercialB)
            ->get(route('commercial.equipements.index'))
            ->assertOk()
            ->assertSee('Chaise de conférence');
    }

    public function test_commercial_can_edit_another_commercial_equipment(): void
    {
        $commercialA = User::factory()->create([
            'role' => 'commercial',
        ]);

        $commercialB = User::factory()->create([
            'role' => 'commercial',
        ]);

        $equipment = $this->createEquipment($commercialA->id);

        $this->actingAs($commercialB)
            ->put(
                route('commercial.equipements.update', $equipment),
                [
                    'name' => 'Chaise premium',
                    'category' => 'Mobilier',
                    'description' => 'Modèle amélioré',
                    'price' => 200,
                    'quantity_available' => 30,
                    'is_active' => 1,
                ]
            )
            ->assertRedirect(route('commercial.equipements.index'));

        $this->assertDatabaseHas('equipment', [
            'id' => $equipment->id,
            'name' => 'Chaise premium',
            'commercial_id' => $commercialA->id,
        ]);
    }

    public function test_commercial_can_see_admin_equipment(): void
    {
        $commercial = User::factory()->create([
            'role' => 'commercial',
        ]);

        $this->createEquipment(null);

        $this->actingAs($commercial)
            ->get(route('commercial.equipements.index'))
            ->assertOk()
            ->assertSee('Chaise de conférence');
    }

    public function test_admin_sees_commercial_equipment(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $commercial = User::factory()->create([
            'role' => 'commercial',
        ]);

        $this->createEquipment($commercial->id);

        $this->actingAs($admin)
            ->get(route('admin.equipements.index'))
            ->assertOk()
            ->assertSee('Chaise de conférence');
    }
}
