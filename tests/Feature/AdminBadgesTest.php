<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Exhibitor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBadgesTest extends TestCase
{
    use RefreshDatabase;

    private function createExhibitor(int $quota = 3): Exhibitor
    {
        $commercial = User::factory()->create([
            'role' => 'commercial',
            'is_active' => true,
        ]);

        return $commercial->exhibitors()->create([
            'company_name' => 'Atlas Froid',
            'contact_name' => 'Ahmed Benali',
            'email' => 'contact-atlas@example.test',
            'phone' => '0600000000',
            'stand_number' => 'A12',
            'area_sqm' => 24,
            'authorized_badges' => $quota,
        ]);
    }

    private function badgeData(): array
    {
        return [
            'first_name' => 'Ahmed',
            'last_name' => 'Benali',
            'function' => 'Directeur technique',
            'email' => 'ahmed@example.test',
            'phone' => '0612345678',
        ];
    }

    private function createBadge(Exhibitor $exhibitor): Badge
    {
        return $exhibitor->badges()->create($this->badgeData());
    }

    public function test_admin_can_open_badges_and_create_badge(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $exhibitor = $this->createExhibitor();

        $this->actingAs($admin)
            ->get(route('admin.badges.index'))
            ->assertOk()
            ->assertSee('Gestion des badges');

        $this->get(route('admin.badges.create'))
            ->assertOk()
            ->assertSee('Créer un badge');

        $this->post(route('admin.badges.store'), [
            'exhibitor_id' => $exhibitor->id,
            ...$this->badgeData(),
        ])->assertRedirect();

        $badge = Badge::firstOrFail();

        $this->assertDatabaseHas('badges', [
            'id' => $badge->id,
            'exhibitor_id' => $exhibitor->id,
            'first_name' => 'Ahmed',
            'last_name' => 'Benali',
            'status' => 'active',
        ]);

        $this->assertNotNull($badge->qr_token);

        $this->assertDatabaseHas('badge_activity_logs', [
            'badge_id' => $badge->id,
            'user_id' => $admin->id,
            'action' => 'created',
        ]);
    }

    public function test_admin_cannot_exceed_badge_quota(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $exhibitor = $this->createExhibitor(1);

        $this->createBadge($exhibitor);

        $this->actingAs($admin)
            ->post(route('admin.badges.store'), [
                'exhibitor_id' => $exhibitor->id,
                ...$this->badgeData(),
            ])
            ->assertSessionHasErrors('exhibitor_id');

        $this->assertDatabaseCount('badges', 1);
    }

    public function test_admin_can_update_badge(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $exhibitor = $this->createExhibitor();
        $badge = $this->createBadge($exhibitor);

        $this->actingAs($admin)
            ->put(route('admin.badges.update', $badge), [
                ...$this->badgeData(),
                'first_name' => 'Youssef',
                'function' => 'Responsable technique',
            ])
            ->assertRedirect(route('admin.badges.show', $badge));

        $this->assertDatabaseHas('badges', [
            'id' => $badge->id,
            'first_name' => 'Youssef',
            'function' => 'Responsable technique',
        ]);

        $this->assertDatabaseHas('badge_activity_logs', [
            'badge_id' => $badge->id,
            'action' => 'updated',
        ]);
    }

    public function test_admin_can_deactivate_and_archive_badge(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $exhibitor = $this->createExhibitor(1);
        $badge = $this->createBadge($exhibitor);

        $this->actingAs($admin)
            ->patch(route('admin.badges.toggle', $badge))
            ->assertRedirect();

        $this->assertSame('inactive', $badge->fresh()->status);

        $this->patch(route('admin.badges.toggle', $badge))
            ->assertRedirect();

        $this->assertSame('active', $badge->fresh()->status);

        $this->delete(route('admin.badges.destroy', $badge))
            ->assertRedirect(route('admin.badges.index'));

        $this->assertSoftDeleted('badges', [
            'id' => $badge->id,
        ]);

        $this->assertDatabaseHas('badge_activity_logs', [
            'badge_id' => $badge->id,
            'action' => 'archived',
        ]);

        $this->assertSame(0, $exhibitor->badges()->count());
    }

    public function test_admin_cannot_reduce_quota_below_existing_badges(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $exhibitor = $this->createExhibitor(3);

        $this->createBadge($exhibitor);
        $this->createBadge($exhibitor);

        $this->actingAs($admin)
            ->patch(route('admin.badges.quota', $exhibitor), [
                'authorized_badges' => 1,
            ])
            ->assertSessionHasErrors('authorized_badges');

        $this->assertSame(
            3,
            $exhibitor->fresh()->authorized_badges
        );

        $this->patch(route('admin.badges.quota', $exhibitor), [
            'authorized_badges' => 2,
        ])->assertRedirect();

        $this->assertSame(
            2,
            $exhibitor->fresh()->authorized_badges
        );
    }

    public function test_admin_can_verify_print_and_export_badges(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $exhibitor = $this->createExhibitor();
        $badge = $this->createBadge($exhibitor);

        $this->actingAs($admin)
            ->get(route('admin.badges.verify', $badge->qr_token))
            ->assertOk()
            ->assertSee('Badge valide');

        $this->get(route('admin.badges.show', $badge))
            ->assertOk()
            ->assertSee('Ahmed Benali');

        $this->get(route('admin.badges.print', $badge))
            ->assertOk()
            ->assertSee('REFRIGAIR EXPO');

        $this->get(route('admin.badges.print-selected', [
            'ids' => [$badge->id],
        ]))
            ->assertOk()
            ->assertSee('Ahmed Benali');

        $this->get(route('admin.badges.export'))
            ->assertOk()
            ->assertDownload();
    }

    public function test_commercial_cannot_access_admin_badges(): void
    {
        $commercial = User::factory()->create([
            'role' => 'commercial',
        ]);

        $this->actingAs($commercial)
            ->get(route('admin.badges.index'))
            ->assertForbidden();

        $this->post(route('admin.badges.store'), [
            'exhibitor_id' => 1,
            ...$this->badgeData(),
        ])->assertForbidden();
    }
}
