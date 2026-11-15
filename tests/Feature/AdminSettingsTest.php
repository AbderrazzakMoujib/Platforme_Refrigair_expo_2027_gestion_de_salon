<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_and_update_own_profile(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Original',
            'email' => 'admin-original@example.test',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.parametres.index'))
            ->assertOk()
            ->assertSee('Informations personnelles')
            ->assertSee('Modifier le mot de passe');

        $this->put(route('admin.parametres.profil'), [
            'name' => 'Admin Refrigair',
            'email' => 'admin-updated@example.test',
            'phone' => '+212600000000',
        ])
            ->assertRedirect(route('admin.parametres.index'))
            ->assertSessionHas('profile_status');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Admin Refrigair',
            'email' => 'admin-updated@example.test',
            'phone' => '+212600000000',
            'role' => 'admin',
        ]);
    }

    public function test_admin_cannot_use_another_users_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $other = User::factory()->create([
            'email' => 'other@example.test',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.parametres.profil'), [
                'name' => 'Admin Updated',
                'email' => $other->email,
                'phone' => '',
            ])
            ->assertSessionHasErrors(['email'], null, 'profile');

        $this->assertSame(
            $admin->email,
            $admin->fresh()->email
        );
    }

    public function test_admin_can_change_password_with_current_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'old-password-123',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.parametres.password'), [
                'current_password' => 'old-password-123',
                'password' => 'new-secure-password-123',
                'password_confirmation' => 'new-secure-password-123',
            ])
            ->assertRedirect(route('admin.parametres.index'))
            ->assertSessionHas('password_status');

        $this->assertTrue(
            Hash::check(
                'new-secure-password-123',
                $admin->fresh()->password
            )
        );
    }

    public function test_admin_cannot_change_password_with_wrong_current_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'old-password-123',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.parametres.password'), [
                'current_password' => 'wrong-password',
                'password' => 'new-secure-password-123',
                'password_confirmation' => 'new-secure-password-123',
            ])
            ->assertSessionHasErrors(['current_password'], null, 'password');

        $this->assertTrue(
            Hash::check(
                'old-password-123',
                $admin->fresh()->password
            )
        );
    }

    public function test_commercial_cannot_access_admin_settings(): void
    {
        $commercial = User::factory()->create([
            'role' => 'commercial',
        ]);

        $this->actingAs($commercial)
            ->get(route('admin.parametres.index'))
            ->assertForbidden();

        $this->put(route('admin.parametres.profil'), [
            'name' => 'Hacked',
            'email' => 'hacked@example.test',
        ])
            ->assertForbidden();

        $this->put(route('admin.parametres.password'), [
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
            ->assertForbidden();
    }
}
