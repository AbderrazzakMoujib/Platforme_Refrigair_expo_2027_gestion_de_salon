<?php

namespace Tests\Feature;

use App\Models\EquipmentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_receives_notifications_for_new_exhibitors_and_requests(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $commercial = User::factory()->create([
            'role' => 'commercial',
        ]);

        $exhibitor = $commercial->exhibitors()->create([
            'company_name' => 'Atlas Froid',
            'contact_name' => 'Sara Alami',
            'email' => 'atlas-notification@example.test',
            'phone' => '+212600000000',
            'stand_number' => 'A12',
            'area_sqm' => 24,
            'authorized_badges' => 3,
        ]);

        $this->assertSame(
            1,
            $admin->unreadNotifications()->count()
        );

        $order = EquipmentRequest::create([
            'exhibitor_id' => $exhibitor->id,
            'status' => 'pending',
            'total_amount' => '500.00',
        ]);

        $this->assertSame(
            2,
            $admin->unreadNotifications()->count()
        );

        $this->actingAs($admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertViewHas('totalCount', 2)
            ->assertViewHas('unreadCount', 2)
            ->assertSee('Atlas Froid')
            ->assertSee('Nouvelle demande');

        $notification = $admin->notifications()
            ->where('data', 'like', '%equipment_request%')
            ->firstOrFail();

        $this->assertSame(
            $order->id,
            $notification->data['equipment_request_id']
        );

        $this->post(
            route('admin.notifications.open', $notification->id)
        )->assertRedirect(
            route('admin.demandes.show', $order)
        );

        $this->assertNotNull(
            $notification->fresh()->read_at
        );

        $this->assertSame(
            1,
            $admin->unreadNotifications()->count()
        );

        $this->post(
            route('admin.notifications.read-all')
        )->assertRedirect();

        $this->assertSame(
            0,
            $admin->unreadNotifications()->count()
        );
    }

    public function test_commercial_cannot_access_admin_notifications(): void
    {
        $commercial = User::factory()->create([
            'role' => 'commercial',
        ]);

        $this->actingAs($commercial)
            ->get(route('admin.notifications.index'))
            ->assertForbidden();

        $this->post(
            route('admin.notifications.read-all')
        )->assertForbidden();
    }

    public function test_admin_cannot_read_another_admin_notification(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);

        $notification = $admin1->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => \App\Notifications\NewExhibitorNotification::class,
            'data' => [
                'type' => 'exhibitor',
                'title' => 'Test',
                'message' => 'Notification privée',
                'exhibitor_id' => 999,
            ],
        ]);

        $this->actingAs($admin2)
            ->patch(route(
                'admin.notifications.read',
                $notification->id
            ))
            ->assertNotFound();

        $this->assertNull(
            $notification->fresh()->read_at
        );
    }
}
