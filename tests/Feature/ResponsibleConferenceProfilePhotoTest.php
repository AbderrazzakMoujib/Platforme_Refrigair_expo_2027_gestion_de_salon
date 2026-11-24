<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResponsibleConferenceProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    private function manager(array $attributes = []): User
    {
        return User::factory()->create([
            'role' => UserRole::ResponsableConferences,
            'is_active' => true,
            ...$attributes,
        ]);
    }

    public function test_admin_can_create_a_manager_with_a_profile_photo(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.responsables-conferences.create'))
            ->assertOk()
            ->assertSee('manager-profile-photo')
            ->assertSee('multipart/form-data');

        $this->post(route('admin.responsables-conferences.store'), [
            'name' => 'Responsable Photo',
            'email' => 'photo-manager@example.test',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
            'profile_photo' => UploadedFile::fake()->image('portrait.jpg'),
        ])->assertRedirect(route('admin.responsables-conferences.index'));

        $manager = User::query()->where('email', 'photo-manager@example.test')->firstOrFail();
        $this->assertStringStartsWith('responsables-conferences/', $manager->profile_photo);
        Storage::disk('public')->assertExists($manager->profile_photo);

        $this->get(route('admin.responsables-conferences.index'))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($manager->profile_photo), false);
    }

    public function test_admin_can_replace_and_remove_a_photo_after_saving(): void
    {
        Storage::fake('public');
        $oldPath = UploadedFile::fake()->image('old.png')->store('responsables-conferences', 'public');
        $manager = $this->manager(['profile_photo' => $oldPath]);

        $this->actingAs($this->admin())
            ->put(route('admin.responsables-conferences.update', $manager), [
                'name' => $manager->name,
                'email' => $manager->email,
                'profile_photo' => UploadedFile::fake()->image('new.webp'),
            ])->assertRedirect(route('admin.responsables-conferences.index'));

        $newPath = $manager->fresh()->profile_photo;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);

        $this->put(route('admin.responsables-conferences.update', $manager), [
            'name' => $manager->name,
            'email' => $manager->email,
            'remove_profile_photo' => '1',
        ])->assertRedirect(route('admin.responsables-conferences.index'));

        $this->assertNull($manager->fresh()->profile_photo);
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_shared_photo_is_not_deleted_when_one_manager_removes_it(): void
    {
        Storage::fake('public');
        $sharedPath = UploadedFile::fake()->image('shared.jpg')->store('responsables-conferences', 'public');
        $manager = $this->manager(['profile_photo' => $sharedPath]);
        User::factory()->create(['role' => UserRole::Commercial, 'profile_photo' => $sharedPath]);

        $this->actingAs($this->admin())
            ->put(route('admin.responsables-conferences.update', $manager), [
                'name' => $manager->name,
                'email' => $manager->email,
                'remove_profile_photo' => '1',
            ])->assertRedirect();

        $this->assertNull($manager->fresh()->profile_photo);
        Storage::disk('public')->assertExists($sharedPath);
    }

    public function test_manager_can_change_own_photo_without_changing_role_or_permissions(): void
    {
        Storage::fake('public');
        $manager = $this->manager();

        $this->actingAs($manager)
            ->get(route('responsable-conferences.profile'))
            ->assertOk()
            ->assertSee('manager-own-profile-photo')
            ->assertSee('multipart/form-data')
            ->assertSee($manager->initials);

        $this->put(route('responsable-conferences.profile.update'), [
            'name' => $manager->name,
            'email' => $manager->email,
            'role' => UserRole::Admin->value,
            'profile_photo' => UploadedFile::fake()->image('own-photo.png'),
        ])->assertRedirect();

        $manager->refresh();
        $this->assertSame(UserRole::ResponsableConferences, $manager->role);
        $this->assertStringStartsWith('responsables-conferences/', $manager->profile_photo);
        Storage::disk('public')->assertExists($manager->profile_photo);
        $this->get(route('admin.index'))->assertForbidden();
    }

    public function test_invalid_photo_type_and_size_are_rejected(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $manager = $this->manager();

        $this->actingAs($admin)
            ->post(route('admin.responsables-conferences.store'), [
                'name' => 'Bad Photo',
                'email' => 'bad-photo@example.test',
                'password' => 'StrongPass123!',
                'password_confirmation' => 'StrongPass123!',
                'profile_photo' => UploadedFile::fake()->create('portrait.gif', 100, 'image/gif'),
            ])->assertSessionHasErrors('profile_photo');

        $this->put(route('admin.responsables-conferences.update', $manager), [
            'name' => $manager->name,
            'email' => $manager->email,
            'profile_photo' => UploadedFile::fake()->image('large.png')->size(2049),
        ])->assertSessionHasErrors('profile_photo');

        $this->assertDatabaseMissing('users', ['email' => 'bad-photo@example.test']);
        $this->assertNull($manager->fresh()->profile_photo);
        $this->assertSame([], Storage::disk('public')->allFiles('responsables-conferences'));
    }

    public function test_only_admin_or_the_manager_can_change_a_manager_photo(): void
    {
        Storage::fake('public');
        $manager = $this->manager();
        $commercial = User::factory()->create(['role' => UserRole::Commercial]);

        $this->actingAs($commercial)
            ->put(route('admin.responsables-conferences.update', $manager), [
                'name' => $manager->name,
                'email' => $manager->email,
                'profile_photo' => UploadedFile::fake()->image('unauthorized.png'),
            ])->assertForbidden();

        $this->actingAs($this->admin())
            ->put(route('responsable-conferences.profile.update'), [
                'name' => $manager->name,
                'email' => $manager->email,
                'remove_profile_photo' => '1',
            ])->assertForbidden();

        $this->assertNull($manager->fresh()->profile_photo);
        $this->assertSame([], Storage::disk('public')->allFiles('responsables-conferences'));
    }
}
