<?php

namespace Tests\Feature;

use App\Models\Exhibitor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExhibitorCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $role, string $email): User
    {
        return User::factory()->create(['role' => $role, 'email' => $email, 'is_active' => true]);
    }

    private function exhibitor(User $commercial, string $name): Exhibitor
    {
        $exhibitor = new Exhibitor([
            'commercial_id' => $commercial->id,
            'company_name' => $name,
            'contact_name' => 'Contact Test',
            'email' => strtolower(str_replace(' ', '.', $name)).'@example.test',
            'phone' => '+212600000000',
        ]);
        $exhibitor->commercial_id = $commercial->id;
        $exhibitor->save();
        return $exhibitor;
    }

    public function test_exhibitor_can_save_catalog_profile_and_upload_logo_without_changing_primary_profile(): void
    {
        Storage::fake('local');
        $commercial = $this->account('commercial', 'commercial@example.test');
        $user = $this->account('exposant', 'login@example.test');
        $exhibitor = $this->exhibitor($commercial, 'Maison Test');
        $exhibitor->user()->associate($user);
        $exhibitor->save();

        $this->actingAs($user)->get(route('exhibitor.catalog-profile.edit'))->assertOk()
            ->assertSee('Fiche Catalogue de l', false)
            ->assertSee('m12 2 9 5-9 5-9-5 9-5', false);
        $this->actingAs($user)->put(route('exhibitor.catalog-profile.update'), [
            'company_description' => 'Une entreprise de démonstration.',
            'website' => 'https://example.test',
            'email' => 'catalogue@example.test',
            'phone' => '+212611111111',
            'logo' => UploadedFile::fake()->image('logo.png'),
            'action' => 'complete',
        ])->assertRedirect(route('exhibitor.catalog-profile.edit'));

        $this->assertDatabaseHas('exhibitor_catalog_profiles', ['exhibitor_id' => $exhibitor->id, 'status' => 'completed', 'email' => 'catalogue@example.test']);
        $profile = $exhibitor->fresh()->catalogProfile;
        $this->assertNotNull($profile->logo_path);
        Storage::disk('local')->assertExists($profile->logo_path);
        $this->assertSame('login@example.test', $user->fresh()->email);
        $this->assertSame('maison.test@example.test', $exhibitor->fresh()->email);
    }

    public function test_catalog_upload_validation_and_logo_removal(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $commercial = $this->account('commercial', 'commercial2@example.test');
        $user = $this->account('exposant', 'exhibitor2@example.test');
        $exhibitor = $this->exhibitor($commercial, 'Logo Test');
        $exhibitor->logo = 'source-logo.png';
        $exhibitor->user()->associate($user)->save();
        Storage::disk('public')->put('source-logo.png', 'original-profile-logo');

        $this->actingAs($user)->put(route('exhibitor.catalog-profile.update'), [
            'company_description' => 'Description', 'email' => 'catalog@example.test', 'action' => 'draft',
            'logo' => UploadedFile::fake()->create('logo.svg', 20, 'image/svg+xml'),
        ])->assertSessionHasErrors('logo');
        $this->assertDatabaseMissing('exhibitor_catalog_profiles', ['exhibitor_id' => $exhibitor->id]);
        $this->put(route('exhibitor.catalog-profile.update'), [
            'company_description' => str_repeat('a', 1001), 'email' => 'catalog@example.test', 'action' => 'draft',
            'logo' => UploadedFile::fake()->image('large.jpg')->size(2049),
        ])->assertSessionHasErrors(['company_description', 'logo']);

        $this->put(route('exhibitor.catalog-profile.update'), [
            'company_description' => 'Description', 'email' => 'catalog@example.test', 'action' => 'draft',
            'logo' => UploadedFile::fake()->image('logo.jpg'),
        ])->assertRedirect();
        $oldPath = $exhibitor->fresh()->catalogProfile->logo_path;
        $this->put(route('exhibitor.catalog-profile.update'), [
            'company_description' => 'Description', 'email' => 'catalog@example.test', 'action' => 'draft',
            'logo' => UploadedFile::fake()->create('invalid.svg', 20, 'image/svg+xml'),
        ])->assertSessionHasErrors('logo');
        $this->assertSame($oldPath, $exhibitor->fresh()->catalogProfile->logo_path);
        Storage::disk('local')->assertExists($oldPath);
        $this->put(route('exhibitor.catalog-profile.update'), [
            'company_description' => 'Description', 'email' => 'catalog@example.test', 'action' => 'draft',
            'logo' => UploadedFile::fake()->image('replacement.webp'),
        ])->assertRedirect();
        $replacementPath = $exhibitor->fresh()->catalogProfile->logo_path;
        $this->assertNotSame($oldPath, $replacementPath);
        Storage::disk('local')->assertMissing($oldPath);
        $this->put(route('exhibitor.catalog-profile.update'), ['action' => 'draft', 'remove_logo' => '1'])->assertRedirect();
        Storage::disk('local')->assertMissing($replacementPath);
        $this->assertNull($exhibitor->fresh()->catalogProfile->logo_path);
        $this->assertSame('source-logo.png', $exhibitor->fresh()->logo, 'Le logo du profil principal ne doit pas être supprimé.');
        $page = $this->get(route('exhibitor.catalog-profile.edit'))->assertOk()->getContent();
        preg_match('/id="catalog-logo-preview">(.*?)<\/div>/s', $page, $preview);
        $this->assertStringContainsString('LO', $preview[1] ?? '', 'La fiche catalogue doit afficher son avatar après retrait du logo.');
    }

    public function test_exhibitor_cannot_edit_another_exhibitors_catalog_or_change_stand_fields(): void
    {
        Storage::fake('local');
        $commercial = $this->account('commercial', 'commercial3@example.test');
        $owner = $this->account('exposant', 'owner@example.test');
        $other = $this->account('exposant', 'other@example.test');
        $owned = $this->exhibitor($commercial, 'Owned Company');
        $owned->user()->associate($owner)->save();
        $this->exhibitor($commercial, 'Other Company')->user()->associate($other)->save();

        $this->actingAs($owner)->put(route('exhibitor.catalog-profile.update'), [
            'company_description' => 'Owned', 'email' => 'owned@example.test', 'action' => 'draft',
            'stand_number' => 'HACK', 'area_sqm' => 999, 'authorized_badges' => 999,
        ])->assertRedirect();
        $this->assertDatabaseHas('exhibitors', ['id' => $owned->id, 'stand_number' => null, 'area_sqm' => null, 'authorized_badges' => 0]);

        $commercialOwner = $this->account('commercial', 'other-commercial@example.test');
        $foreign = $this->exhibitor($commercialOwner, 'Foreign');
        $this->actingAs($commercial)->get(route('commercial.catalog.show', $foreign->id))->assertNotFound();
        $this->get(route('commercial.catalog.logo', $foreign->id))->assertNotFound();
    }

    public function test_admin_can_list_and_export_catalog_and_other_roles_are_denied_admin_routes(): void
    {
        Storage::fake('local');
        $commercial = $this->account('commercial', 'commercial4@example.test');
        $exhibitor = $this->exhibitor($commercial, 'Export Company');
        $profile = $exhibitor->catalogProfile()->create(['company_description' => 'Description', 'email' => 'export@example.test', 'status' => 'completed']);
        $profile->logo_path = 'exhibitor-catalog/logos/'.$exhibitor->id.'/export-company-'.$exhibitor->id.'.png';
        $profile->save();
        Storage::disk('local')->put($profile->logo_path, 'fake-image-content');
        $admin = $this->account('admin', 'admin@example.test');
        $this->actingAs($admin)->get(route('admin.catalog.index'))->assertOk()->assertSee('Export Company');
        $this->actingAs($admin)->get(route('admin.catalog.export'))->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->get(route('admin.catalog.logos.zip'))->assertOk();
        $this->actingAs($commercial)->get(route('admin.catalog.index'))->assertForbidden();
    }
}
