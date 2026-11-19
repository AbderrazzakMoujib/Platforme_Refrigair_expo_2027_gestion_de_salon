<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\EquipmentRequest;
use App\Models\Exhibitor;
use App\Models\User;
use App\Notifications\NewEquipmentRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercialEquipmentRequestsTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    private function exhibitorFor(User $commercial, string $company, ?string $stand = null): Exhibitor
    {
        $this->sequence++;

        return $commercial->exhibitors()->create([
            'company_name' => $company,
            'contact_name' => 'Contact '.$company,
            'email' => 'contact'.$this->sequence.'@requests.test',
            'phone' => '+212600000000',
            'stand_number' => $stand ?? 'R'.$this->sequence,
            'area_sqm' => 18,
            'authorized_badges' => 2,
        ]);
    }

    private function equipment(string $name, float $price, int $stock, bool $active = true): Equipment
    {
        return Equipment::query()->create([
            'name' => $name,
            'category' => 'Audiovisuel',
            'description' => 'Test',
            'price' => $price,
            'quantity_available' => $stock,
            'is_active' => $active,
        ]);
    }

    private function requestFor(Exhibitor $exhibitor, string $status, Equipment $equipment, int $quantity = 1, array $extra = []): EquipmentRequest
    {
        $order = EquipmentRequest::query()->create([
            'exhibitor_id' => $exhibitor->id,
            'status' => $status,
            'total_amount' => $equipment->price * $quantity,
            ...$extra,
        ]);

        $order->items()->create([
            'equipment_id' => $equipment->id,
            'equipment_name' => $equipment->name,
            'quantity' => $quantity,
            'unit_price' => $equipment->price,
            'subtotal' => $equipment->price * $quantity,
        ]);

        return $order;
    }

    public function test_index_lists_only_own_requests_with_statistics(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $other = User::factory()->create(['role' => 'commercial']);
        $screen = $this->equipment('Écran LED 55', 2500, 10);

        $mine = $this->exhibitorFor($commercial, 'ClimaTech');
        $this->requestFor($mine, 'pending', $screen, 2);
        $this->requestFor($mine, 'approved', $screen);
        $this->requestFor($mine, 'rejected', $screen);
        $this->requestFor($this->exhibitorFor($other, 'Concurrent SA'), 'pending', $screen);

        $this->actingAs($commercial)->get(route('commercial.requests.index'))
            ->assertOk()
            ->assertViewHas('totalCount', 3)
            ->assertViewHas('pendingCount', 1)
            ->assertViewHas('approvedCount', 1)
            ->assertViewHas('rejectedCount', 1)
            ->assertSee('ClimaTech')
            ->assertSee('Écran LED 55')
            ->assertDontSee('Concurrent SA');

        $pending = $this->get(route('commercial.requests.index', ['status' => 'pending']))
            ->assertOk()
            ->viewData('requests');
        $this->assertCount(1, $pending);
        $this->assertSame('pending', $pending->first()->status);
    }

    public function test_search_does_not_leak_other_commercials_requests(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $other = User::factory()->create(['role' => 'commercial']);
        $screen = $this->equipment('Écran', 100, 10);

        $this->requestFor($this->exhibitorFor($commercial, 'Mon Client', 'A-12'), 'pending', $screen);
        $this->requestFor($this->exhibitorFor($other, 'Autre Client', 'A-12'), 'pending', $screen);

        $results = $this->actingAs($commercial)
            ->get(route('commercial.requests.index', ['search' => 'A-12']))
            ->assertOk()
            ->viewData('requests');

        $this->assertSame(['Mon Client'], $results->pluck('exhibitor.company_name')->all());
    }

    public function test_create_page_lists_own_exhibitors_and_active_equipment(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $other = User::factory()->create(['role' => 'commercial']);
        $this->exhibitorFor($commercial, 'ClimaTech');
        $this->exhibitorFor($other, 'Concurrent SA');
        $this->equipment('Borne interactive', 3900, 5);
        $this->equipment('Ancien Totem', 1800, 5, false);

        $this->actingAs($commercial)->get(route('commercial.requests.create'))
            ->assertOk()
            ->assertSee('ClimaTech')
            ->assertDontSee('Concurrent SA')
            ->assertSee('Borne interactive')
            ->assertDontSee('Ancien Totem');
    }

    public function test_commercial_can_create_request_for_own_exhibitor(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $commercial = User::factory()->create(['role' => 'commercial']);
        $exhibitor = $this->exhibitorFor($commercial, 'ClimaTech');
        $screen = $this->equipment('Écran LED 55', 2500, 10);
        $charger = $this->equipment('Borne de recharge', 2000, 3);

        $this->actingAs($commercial)->post(route('commercial.requests.store'), [
            'exhibitor_id' => $exhibitor->id,
            'items' => [$screen->id => 2, $charger->id => 1],
            'notes' => 'Installation le 1er février',
        ])->assertRedirect();

        $order = EquipmentRequest::query()->with('items')->sole();
        $this->assertSame($exhibitor->id, $order->exhibitor_id);
        $this->assertSame('pending', $order->status);
        $this->assertSame('7000.00', $order->total_amount);
        $this->assertCount(2, $order->items);
        $this->assertSame('Installation le 1er février', $order->notes);

        // Le stock ne change qu'à la validation par l'admin.
        $this->assertSame(10, $screen->fresh()->quantity_available);

        // L'admin est notifié comme pour une demande envoyée par l'exposant.
        $this->assertSame(1, $admin->notifications()->where('type', NewEquipmentRequestNotification::class)->count());

        $this->get(route('commercial.requests.show', $order))
            ->assertOk()
            ->assertSee('Écran LED 55')
            ->assertSee('7 000,00 DH');
    }

    public function test_store_rejects_foreign_exhibitor_stock_overflow_and_inactive_equipment(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $other = User::factory()->create(['role' => 'commercial']);
        $mine = $this->exhibitorFor($commercial, 'ClimaTech');
        $foreign = $this->exhibitorFor($other, 'Concurrent SA');
        $screen = $this->equipment('Écran', 100, 2);
        $inactive = $this->equipment('Inactif', 100, 5, false);

        $this->actingAs($commercial);

        $this->post(route('commercial.requests.store'), ['exhibitor_id' => $foreign->id, 'items' => [$screen->id => 1]])
            ->assertSessionHasErrors('exhibitor_id');
        $this->post(route('commercial.requests.store'), ['exhibitor_id' => $mine->id, 'items' => [$screen->id => 3]])
            ->assertSessionHasErrors('items');
        $this->post(route('commercial.requests.store'), ['exhibitor_id' => $mine->id, 'items' => [$inactive->id => 1]])
            ->assertSessionHasErrors('items');
        $this->post(route('commercial.requests.store'), ['exhibitor_id' => $mine->id, 'items' => [$screen->id => 0]])
            ->assertSessionHasErrors('items');

        $this->assertDatabaseCount('equipment_requests', 0);
    }

    public function test_show_displays_rejection_reason_and_blocks_other_commercials(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $other = User::factory()->create(['role' => 'commercial']);
        $screen = $this->equipment('Écran', 100, 10);

        $order = $this->requestFor($this->exhibitorFor($commercial, 'ClimaTech'), 'rejected', $screen, 1, [
            'rejection_reason' => 'Stock réservé pour le hall B',
            'reviewed_at' => now(),
        ]);

        $this->actingAs($commercial)->get(route('commercial.requests.show', $order))
            ->assertOk()
            ->assertSee('Motif du refus')
            ->assertSee('Stock réservé pour le hall B');

        $this->actingAs($other)->get(route('commercial.requests.show', $order))->assertNotFound();
    }

    public function test_pages_are_forbidden_to_other_roles(): void
    {
        $this->get(route('commercial.requests.index'))->assertRedirect(route('login'));

        foreach (['admin', 'exposant'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get(route('commercial.requests.index'))->assertForbidden();
            $this->actingAs($user)->get(route('commercial.requests.create'))->assertForbidden();
        }
    }

    public function test_sidebar_links_to_requests_page(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);

        $this->actingAs($commercial)->get(route('commercial.index'))
            ->assertOk()
            ->assertSee(route('commercial.requests.index'));
    }
}
