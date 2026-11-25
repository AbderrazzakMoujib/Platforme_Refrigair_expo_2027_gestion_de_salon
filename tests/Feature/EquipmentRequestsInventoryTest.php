<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\EquipmentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EquipmentRequestsInventoryTest extends TestCase
{
    use RefreshDatabase;

    private function commercialWithExhibitor(): array
    {
        $commercial = User::factory()->create(['role' => 'commercial', 'is_active' => true]);
        $exhibitor = $commercial->exhibitors()->create([
            'company_name' => 'Expo Atelier',
            'contact_name' => 'Nadia Test',
            'email' => 'atelier@example.test',
            'phone' => '0600000000',
            'stand_number' => 'A01',
            'area_sqm' => 12,
            'authorized_badges' => 2,
        ]);

        return [$commercial, $exhibitor];
    }

    private function equipment(string $name, int $stock, string $price = '25.00', bool $active = true): Equipment
    {
        return Equipment::query()->create([
            'name' => $name,
            'category' => 'Mobilier',
            'price' => $price,
            'quantity_available' => $stock,
            'is_active' => $active,
        ]);
    }

    private function submit(User $commercial, int $exhibitorId, array $items)
    {
        Notification::fake();

        return $this->actingAs($commercial)->post(
            route('commercial.equipment-requests.store'),
            ['exhibitor_id' => $exhibitorId, 'items' => $items]
        );
    }

    public function test_multi_item_request_preserves_stock_and_historical_prices(): void
    {
        [$commercial, $exhibitor] = $this->commercialWithExhibitor();
        $chair = $this->equipment('Chaise historique', 8, '40.00');
        $table = $this->equipment('Table historique', 4, '100.00');

        $this->submit($commercial, $exhibitor->id, [
            $chair->id => 2,
            $table->id => 1,
        ])->assertRedirect(route('commercial.equipment-requests.show', EquipmentRequest::query()->firstOrFail()));

        $order = EquipmentRequest::query()->with('items')->firstOrFail();
        $this->assertSame('pending', $order->status);
        $this->assertSame('180.00', $order->total_amount);
        $this->assertSame(8, $chair->fresh()->quantity_available);
        $this->assertSame(4, $table->fresh()->quantity_available);

        $chair->update(['name' => 'Chaise actuelle', 'price' => '70.00']);
        $this->assertDatabaseHas('equipment_request_items', [
            'equipment_request_id' => $order->id,
            'equipment_id' => $chair->id,
            'equipment_name' => 'Chaise historique',
            'unit_price' => '40.00',
            'subtotal' => '80.00',
        ]);
    }

    public function test_request_rejects_quantity_above_stock_and_inactive_catalog_items(): void
    {
        [$commercial, $exhibitor] = $this->commercialWithExhibitor();
        $few = $this->equipment('Stock limité', 1);
        $inactive = $this->equipment('Désactivé', 10, '25.00', false);

        $this->submit($commercial, $exhibitor->id, [$few->id => 2])
            ->assertSessionHasErrors('items');
        $this->submit($commercial, $exhibitor->id, [$inactive->id => 1])
            ->assertSessionHasErrors('items');

        $this->assertDatabaseCount('equipment_requests', 0);
        $this->assertSame(1, $few->fresh()->quantity_available);
    }

    public function test_competing_pending_requests_cannot_overdraw_stock(): void
    {
        [$commercial, $firstExhibitor] = $this->commercialWithExhibitor();
        $secondExhibitor = $commercial->exhibitors()->create([
            'company_name' => 'Expo Deux',
            'contact_name' => 'Omar Test',
            'email' => 'deux@example.test',
            'phone' => '0600000001',
            'stand_number' => 'A02',
            'area_sqm' => 12,
            'authorized_badges' => 2,
        ]);
        $equipment = $this->equipment('Pièce rare', 1);

        $this->submit($commercial, $firstExhibitor->id, [$equipment->id => 1])
            ->assertRedirect();
        $firstOrder = EquipmentRequest::query()->firstOrFail();
        $this->submit($commercial, $secondExhibitor->id, [$equipment->id => 1])
            ->assertRedirect();
        $secondOrder = EquipmentRequest::query()->latest('id')->firstOrFail();

        $this->assertSame(1, $equipment->fresh()->quantity_available);

        $this->patch(route('commercial.equipment-requests.approve', $firstOrder))
            ->assertRedirect();
        $this->assertSame(0, $equipment->fresh()->quantity_available);

        $this->patch(route('commercial.equipment-requests.approve', $secondOrder))
            ->assertSessionHasErrors('status');
        $this->assertSame('pending', $secondOrder->fresh()->status);
        $this->assertSame(0, $equipment->fresh()->quantity_available);
    }
}
