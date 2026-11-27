<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TechnicalProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_technical_user_can_update_own_profile_and_upload_photo_without_changing_permissions(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'role' => UserRole::ResponsableTechnique,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->get(route('responsable-technique.profile'))->assertOk()->assertSee('Sécurité du compte');

        $this->put(route('responsable-technique.profile.update'), [
            'name' => 'Nadia Benali',
            'email' => 'nadia.benali@example.test',
            'phone' => '+212600000000',
            'role' => UserRole::Admin->value,
            'is_active' => false,
            'profile_photo' => UploadedFile::fake()->image('portrait.webp')->size(400),
        ])->assertRedirect(route('responsable-technique.profile'));

        $user->refresh();
        $this->assertSame('Nadia Benali', $user->name);
        $this->assertSame('nadia.benali@example.test', $user->email);
        $this->assertSame('+212600000000', $user->phone);
        $this->assertSame(UserRole::ResponsableTechnique, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertNull($user->email_verified_at);
        $this->assertStringStartsWith('responsables-techniques/', $user->profile_photo);
        Storage::disk('public')->assertExists($user->profile_photo);
    }

    public function test_technical_user_can_remove_own_photo_without_deleting_a_shared_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('shared/avatar.jpg', 'image');
        $user = User::factory()->create(['role' => UserRole::ResponsableTechnique, 'profile_photo' => 'shared/avatar.jpg']);
        $other = User::factory()->create(['profile_photo' => 'shared/avatar.jpg']);

        $this->actingAs($user)->put(route('responsable-technique.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'remove_profile_photo' => '1',
        ])->assertRedirect(route('responsable-technique.profile'));

        $this->assertNull($user->fresh()->profile_photo);
        Storage::disk('public')->assertExists('shared/avatar.jpg');
        $this->assertSame('shared/avatar.jpg', $other->fresh()->profile_photo);
    }

    public function test_profile_rejects_invalid_photo_and_duplicate_email(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => UserRole::ResponsableTechnique]);
        $other = User::factory()->create();

        $this->actingAs($user)->from(route('responsable-technique.profile'))->put(route('responsable-technique.profile.update'), [
            'name' => 'Updated',
            'email' => $other->email,
            'profile_photo' => UploadedFile::fake()->create('avatar.gif', 20, 'image/gif'),
        ])->assertSessionHasErrorsIn('profile', ['email', 'profile_photo']);
    }

    public function test_only_the_technical_role_can_access_profile_and_change_password(): void
    {
        $this->get(route('responsable-technique.profile'))->assertRedirect(route('login'));

        foreach ([UserRole::Commercial, UserRole::Admin, UserRole::Exposant, UserRole::ResponsableConferences] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get(route('responsable-technique.profile'))->assertForbidden();
        }
    }

    public function test_technical_user_can_change_password_only_after_current_password_validation(): void
    {
        $user = User::factory()->create(['role' => UserRole::ResponsableTechnique, 'password' => Hash::make('current-password')]);

        $this->actingAs($user)->put(route('responsable-technique.profile.password'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasErrorsIn('passwordUpdate', ['current_password']);
        $this->assertTrue(Hash::check('current-password', $user->fresh()->password));

        $this->put(route('responsable-technique.profile.password'), [
            'current_password' => 'current-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('responsable-technique.profile'));

        $user->refresh();
        $this->assertTrue(Hash::check('new-password-123', $user->password));
        $this->assertSame(UserRole::ResponsableTechnique, $user->role);
    }
}
