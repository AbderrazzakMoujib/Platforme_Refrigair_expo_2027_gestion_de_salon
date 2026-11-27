<?php

namespace Tests\Feature;

use App\Models\Exhibitor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SlotFixtures;
use Tests\TestCase;

class ExhibitorProfileTest extends TestCase
{
    use RefreshDatabase, SlotFixtures;

    public function test_only_an_exhibitor_can_open_their_profile_page(): void
    {
        $exhibitor = $this->exhibitor('ClimaTech');
        $commercial = $this->commercial();

        $this->get(route('exhibitor.profile'))->assertRedirect(route('login'));

        $this->actingAs($exhibitor->user)
            ->get(route('exhibitor.profile'))
            ->assertOk()
            ->assertSee('Mon Profil')
            ->assertSee('ClimaTech')
            ->assertSee($exhibitor->user->email)
            ->assertSee('logo')
            ->assertSee(route('exhibitor.profile.update'));

        $this->actingAs($commercial)->get(route('exhibitor.profile'))->assertForbidden();
    }

    public function test_exhibitor_can_update_their_contact_details_and_logo_without_changing_stand_or_login_email(): void
    {
        Storage::fake('public');

        $exhibitor = $this->exhibitor('ClimaTech');
        $other = $this->exhibitor('Autre Société');
        $loginEmail = $exhibitor->user->email;
        $commercialId = $exhibitor->commercial_id;
        $oldLogo = 'exhibitors/logos/old-logo.png';
        Storage::disk('public')->put($oldLogo, 'old logo bytes');
        $exhibitor->update(['logo' => $oldLogo]);

        $this->actingAs($exhibitor->user)
            ->put(route('exhibitor.profile.update'), [
                'contact_name' => 'Nouvelle Responsable',
                'email' => 'contact@climatech.test',
                'phone' => '+212600123456',
                'logo' => UploadedFile::fake()->image('logo.png'),
                // Champs non autorisés envoyés volontairement : ils doivent être ignorés.
                'exhibitor_id' => $other->id,
                'company_name' => 'Entreprise modifiée',
                'stand_number' => 'ZZ99',
                'area_sqm' => 999,
                'authorized_badges' => 999,
                'commercial_id' => null,
                'user_id' => $other->user_id,
            ])
            ->assertRedirect(route('exhibitor.profile'))
            ->assertSessionHas('status');

        $updated = $exhibitor->fresh();
        $this->assertSame('Nouvelle Responsable', $updated->contact_name);
        $this->assertSame('contact@climatech.test', $updated->email);
        $this->assertSame('+212600123456', $updated->phone);
        $this->assertSame('ClimaTech', $updated->company_name);
        $this->assertSame($exhibitor->stand_number, $updated->stand_number);
        $this->assertSame($exhibitor->area_sqm, $updated->area_sqm);
        $this->assertSame($exhibitor->authorized_badges, $updated->authorized_badges);
        $this->assertSame($commercialId, $updated->commercial_id);
        $this->assertSame($exhibitor->user_id, $updated->user_id);
        $this->assertSame($loginEmail, $exhibitor->user->fresh()->email);
        $this->assertNotSame($oldLogo, $updated->logo);
        $this->assertStringStartsWith('exhibitors/logos/', $updated->logo);
        Storage::disk('public')->assertExists($updated->logo);
        Storage::disk('public')->assertMissing($oldLogo);

        $logoUrl = Storage::disk('public')->url($updated->logo);
        $this->actingAs($exhibitor->user)
            ->get(route('exhibitor.profile'))
            ->assertOk()
            ->assertSee($logoUrl);
        $this->actingAs($exhibitor->user)
            ->get(route('exhibitor.company.index'))
            ->assertOk()
            ->assertSee($logoUrl);
        $this->actingAs($exhibitor->user)
            ->get(route('exposant.index'))
            ->assertOk()
            ->assertSee($logoUrl);

        $other->refresh();
        $this->assertSame('Autre Société', $other->company_name);
        $this->assertNull($other->logo);
    }

    public function test_invalid_logo_is_rejected_and_does_not_replace_the_existing_logo(): void
    {
        Storage::fake('public');
        $exhibitor = $this->exhibitor('ClimaTech');
        $oldLogo = 'exhibitors/logos/current-logo.png';
        Storage::disk('public')->put($oldLogo, 'previous logo');
        $exhibitor->update(['logo' => $oldLogo]);

        $this->actingAs($exhibitor->user)
            ->from(route('exhibitor.profile'))
            ->put(route('exhibitor.profile.update'), [
                'contact_name' => 'Tentative',
                'email' => 'contact@invalid.test',
                'phone' => '+212600000000',
                'logo' => UploadedFile::fake()->create('unsupported-logo.gif', 10, 'image/gif'),
            ])
            ->assertRedirect(route('exhibitor.profile'))
            ->assertSessionHasErrorsIn('profile', ['logo']);

        $this->assertSame($oldLogo, $exhibitor->fresh()->logo);
        $this->assertSame('Contact ClimaTech', $exhibitor->fresh()->contact_name);
        Storage::disk('public')->assertExists($oldLogo);
        $this->actingAs($exhibitor->user)
            ->get(route('exhibitor.company.index'))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($oldLogo));
    }

    public function test_storage_failure_keeps_the_previous_logo_and_profile_values(): void
    {
        Storage::fake('public');
        $exhibitor = $this->exhibitor('ClimaTech');
        $oldLogo = 'exhibitors/logos/current-logo.png';
        $disk = Storage::disk('public');
        $disk->put($oldLogo, 'previous logo');
        $exhibitor->update(['logo' => $oldLogo]);

        $failingDisk = \Mockery::mock($disk)->makePartial();
        $failingDisk->shouldReceive('putFileAs')->once()->andReturn(false);
        Storage::shouldReceive('disk')->with('public')->andReturn($failingDisk);

        $this->actingAs($exhibitor->user)
            ->from(route('exhibitor.profile'))
            ->put(route('exhibitor.profile.update'), [
                'contact_name' => 'Nouvelle valeur non enregistrée',
                'email' => 'new-contact@climatech.test',
                'phone' => '+212600987654',
                'logo' => UploadedFile::fake()->image('new-logo.png'),
            ])
            ->assertRedirect(route('exhibitor.profile'))
            ->assertSessionHasErrorsIn('profile', ['logo']);

        $this->assertSame($oldLogo, $exhibitor->fresh()->logo);
        $this->assertSame('Contact ClimaTech', $exhibitor->fresh()->contact_name);
        $this->assertSame('contact1@slots.test', $exhibitor->fresh()->email);
        $disk->assertExists($oldLogo);
    }
}
