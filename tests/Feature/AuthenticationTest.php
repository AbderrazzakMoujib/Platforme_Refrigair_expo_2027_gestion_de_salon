<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Exhibitor;
use App\Models\Equipment;
use App\Models\EquipmentRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_admin_can_login_and_open_admin_space(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.index'))->assertOk();
    }

    
public function test_admin_dashboard_uses_real_statistics(): void
{
    $admin = User::factory()->create(['role' => 'admin']);

    $commercial = User::factory()->create([
        'role' => 'commercial',
        'is_active' => true,
    ]);

    $exhibitor = $commercial->exhibitors()->create([
        'company_name' => 'Atlas Froid',
        'contact_name' => 'Mohamed Alami',
        'email' => 'atlas@example.test',
        'phone' => '+212600000000',
        'stand_number' => 'A12',
        'area_sqm' => 24,
        'authorized_badges' => 5,
    ]);

    $exhibitor->badges()->create([
        'first_name' => 'Mohamed',
        'last_name' => 'Alami',
        'function' => 'Manager',
        'email' => 'mohamed@example.test',
    ]);

    $exhibitor->badges()->create([
        'first_name' => 'Sara',
        'last_name' => 'Bennani',
        'function' => 'Commerciale',
        'email' => 'sara@example.test',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.index'))
        ->assertOk()
        ->assertViewHas('commercialsCount', 1)
        ->assertViewHas('exposantsCount', 1)
        ->assertViewHas('totalAuthorizedBadges', 5)
        ->assertViewHas('totalRegisteredBadges', 2)
        ->assertViewHas('remainingBadges', 3)
        ->assertViewHas('partialExhibitors', 1)
        ->assertViewHas('badgeProgress', 40.0)
        ->assertSee('Atlas Froid')
        ->assertSee('Suivi par commercial');
}


    public function test_commercial_dashboard_renders_responsive_empty_states_and_profile_link(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);

        $this->actingAs($commercial)->get(route('commercial.index'))
            ->assertOk()
            ->assertSee('Bonjour, '.$commercial->name)
            ->assertSee('Mes derniers exposants')
            ->assertViewHas('totalRegisteredBadges', 0)
            ->assertViewHas('badgeProgress', 0)
            ->assertSee('Progression générale des badges')
            ->assertSee(route('commercial.profile.edit'))
            ->assertSee('assets/images/icons/favicon_io.png');
    }

    public function test_commercial_can_create_and_update_own_exhibitor(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $payload = [
            'company_name' => 'Froid Atlas', 'contact_name' => 'Sara Amrani',
            'email' => 'sara@froid-atlas.test', 'phone' => '+212600000000',
            'stand_number' => 'A12', 'area_sqm' => '24.5', 'authorized_badges' => '3',
            'password' => 'secure-password', 'password_confirmation' => 'secure-password',
        ];

        $response = $this->actingAs($commercial)->post(route('commercial.exhibitors.store'), $payload);
        $exhibitor = Exhibitor::query()->where('email', $payload['email'])->firstOrFail();
        $response->assertRedirect(route('commercial.exhibitors.show', $exhibitor));
        $this->assertSame($commercial->id, $exhibitor->commercial_id);

        $this->put(route('commercial.exhibitors.update', $exhibitor), [...$payload, 'company_name' => 'Froid Atlas SARL'])
            ->assertRedirect(route('commercial.exhibitors.show', $exhibitor));
        $this->assertDatabaseHas('exhibitors', ['id' => $exhibitor->id, 'company_name' => 'Froid Atlas SARL']);
    }

    public function test_commercial_exhibitor_validation_rejects_bad_email_nonpositive_area_and_negative_badges(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $this->actingAs($commercial)->post(route('commercial.exhibitors.store'), [
            'company_name' => '', 'contact_name' => '', 'email' => 'not-an-email', 'phone' => '',
            'stand_number' => '', 'area_sqm' => '0', 'authorized_badges' => '-1',
        ])->assertSessionHasErrors(['company_name', 'contact_name', 'email', 'phone', 'stand_number', 'area_sqm', 'authorized_badges']);
        $this->assertDatabaseCount('exhibitors', 0);
    }

    public function test_commercial_cannot_view_or_update_another_commercial_exhibitor(): void
    {
        $owner = User::factory()->create(['role' => 'commercial']);
        $other = User::factory()->create(['role' => 'commercial']);
        $exhibitor = $owner->exhibitors()->create([
            'company_name' => 'Private Expo', 'contact_name' => 'Owner Contact',
            'email' => 'owner@private.test', 'phone' => '+212600000000',
            'stand_number' => 'B2', 'area_sqm' => 12, 'authorized_badges' => 2,
        ]);

        $this->actingAs($other)->get(route('commercial.exhibitors.show', $exhibitor))->assertNotFound();
        $this->put(route('commercial.exhibitors.update', $exhibitor), [
            'company_name' => 'Hacked', 'contact_name' => 'Other', 'email' => 'hacked@private.test',
            'phone' => '+212611111111', 'stand_number' => 'X1', 'area_sqm' => 20, 'authorized_badges' => 1,
        ])->assertNotFound();
        $this->assertDatabaseHas('exhibitors', ['id' => $exhibitor->id, 'company_name' => 'Private Expo']);
    }

    public function test_commercial_dashboard_shows_real_exhibitor_count_and_latest_exhibitors(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $commercial->exhibitors()->create([
            'company_name' => 'Atlas Froid', 'contact_name' => 'Yassine', 'email' => 'y@atlas.test',
            'phone' => '+212600000001', 'stand_number' => 'C3', 'area_sqm' => 15, 'authorized_badges' => 1,
        ]);

        $this->actingAs($commercial)->get(route('commercial.index'))->assertOk()
            ->assertSee('Atlas Froid')->assertSee('>1<', false);
    }

    public function test_exhibitor_account_creation_login_linkage_deactivation_and_dashboard_isolation(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $payload = ['company_name'=>'Compte Test','contact_name'=>'Client Test','email'=>'client-account@example.test','phone'=>'+212600000001','stand_number'=>'D4','area_sqm'=>18,'authorized_badges'=>4,'password'=>'secret-pass-123','password_confirmation'=>'secret-pass-123'];
        $this->actingAs($commercial)->post(route('commercial.exhibitors.store'), $payload)->assertRedirect();
        $exhibitor = \App\Models\Exhibitor::where('email', $payload['email'])->firstOrFail();
        $account = $exhibitor->user;
        $this->assertSame('exposant', $account->role->value);
        $this->assertTrue(Hash::check($payload['password'], $account->password));
        $this->post(route('logout'));
        $this->post(route('login.store'), ['email'=>$account->email,'password'=>$payload['password']])->assertRedirect(route('exposant.index'));
        $this->get(route('exposant.index'))->assertOk()->assertSee('Compte Test');
        $this->post(route('logout'));
        $other = User::factory()->create(['role'=>'exposant']);
        $this->actingAs($other)
       ->get(route('exposant.index'))
       ->assertOk()
       ->assertSee('Aucune entreprise associée')
       ->assertDontSee('Compte Test');
        $this->post(route('logout'));
        $this->actingAs($commercial)->patch(route('commercial.exhibitors.access.toggle',$exhibitor))->assertRedirect();
        $this->assertFalse($account->fresh()->is_active);
        $this->patch(route('commercial.exhibitors.access.toggle',$exhibitor));
        $this->assertTrue($account->fresh()->is_active);
    }

    public function test_exhibitor_badges_are_private_and_quota_is_enforced(): void
    {
        $commercial=User::factory()->create(['role'=>'commercial']);
        $owner=User::factory()->create(['role'=>'exposant']);
        $other=User::factory()->create(['role'=>'exposant']);
        $profile=$commercial->exhibitors()->create(['company_name'=>'Badge Co','contact_name'=>'Owner','email'=>'owner@badge.test','phone'=>'1','stand_number'=>'E1','area_sqm'=>10,'authorized_badges'=>1]);
        $owner->exhibitorProfile()->save($profile);
        $body=['first_name'=>'Amina','last_name'=>'Test','function'=>'Manager','email'=>'amina@badge.test'];
        $this->actingAs($owner)->post(route('exhibitor.badges.store'),$body)->assertRedirect();
        $badge=$profile->badges()->firstOrFail();
        $this->post(route('exhibitor.badges.store'),[...$body,'email'=>'second@badge.test'])->assertStatus(422);
        $this->actingAs($other)->get(route('exhibitor.badges.edit',$badge))->assertForbidden();
        $this->delete(route('exhibitor.badges.destroy',$badge))->assertForbidden();
    }

    public function test_admin_must_provide_initial_password_when_creating_commercial(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.commerciaux.store'), [
            'name' => 'Commercial Test',
            'email' => 'commercial@example.test',
            'phone' => '+212600000000',
            'is_active' => '1',
        ])->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'commercial@example.test']);
    }

    public function test_admin_can_update_commercial_information_and_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $commercial = User::factory()->create(['role' => 'commercial', 'is_active' => true]);

        $this->actingAs($admin)->put(route('admin.commerciaux.update', $commercial), [
            'name' => 'Nom Modifié',
            'email' => $commercial->email,
            'phone' => '+212611111111',
            'is_active' => '0',
        ])->assertRedirect(route('admin.commerciaux.show', $commercial));

        $this->assertDatabaseHas('users', [
            'id' => $commercial->id,
            'name' => 'Nom Modifié',
            'phone' => '+212611111111',
            'is_active' => 0,
            'role' => 'commercial',
        ]);
    }

    public function test_non_admin_cannot_access_commercial_management(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);

        $this->actingAs($commercial)->get(route('admin.commerciaux.index'))->assertForbidden();
        $this->actingAs($commercial)->post(route('admin.commerciaux.store'), [
            'name' => 'Intrusion',
            'email' => 'intrusion@example.test',
            'is_active' => '1',
        ])->assertForbidden();
    }

    public function test_admin_creates_commercial_with_hashed_initial_password_and_commercial_can_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.commerciaux.store'), [
            'name' => 'Commercial Dev', 'email' => 'commercial-dev@example.test',
            'phone' => '+212600000000', 'is_active' => '1',
            'password' => 'initial-secret', 'password_confirmation' => 'initial-secret',
        ])->assertRedirect();

        $commercial = User::query()->where('email', 'commercial-dev@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('initial-secret', $commercial->password));
        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => $commercial->email, 'password' => 'initial-secret'])
            ->assertRedirect(route('commercial.index'));
        $this->assertAuthenticatedAs($commercial);
    }

    public function test_commercial_can_change_password_after_verifying_current_password(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial', 'password' => 'current-secret']);

        $this->actingAs($commercial)->put(route('commercial.profile.update'), [
            'current_password' => 'wrong-secret', 'password' => 'new-secret-pass', 'password_confirmation' => 'new-secret-pass',
        ])->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('current-secret', $commercial->fresh()->password));

        $this->put(route('commercial.profile.update'), [
            'current_password' => 'current-secret', 'password' => 'new-secret-pass', 'password_confirmation' => 'new-secret-pass',
        ])->assertSessionHas('status');
        $this->assertTrue(Hash::check('new-secret-pass', $commercial->fresh()->password));
    }

    public function test_inactive_commercial_cannot_login(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial', 'is_active' => false, 'password' => 'secret-pass']);
        $this->post(route('login.store'), ['email' => $commercial->email, 'password' => 'secret-pass'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_commercial_list_filters_by_search_and_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['name' => 'Nadia Active', 'email' => 'nadia@example.test', 'role' => 'commercial', 'is_active' => true]);
        User::factory()->create(['name' => 'Nadia Inactive', 'email' => 'inactive@example.test', 'role' => 'commercial', 'is_active' => false]);

        $this->actingAs($admin)->get(route('admin.commerciaux.index', ['search' => 'nadia', 'status' => 'active']))
            ->assertOk()->assertSee('Nadia Active')->assertDontSee('Nadia Inactive');
    }

    public function test_admin_is_redirected_to_admin_space_even_when_a_different_space_was_requested_before_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get(route('exposant.index'))->assertRedirect(route('login'));

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.index'));
    }

    public function test_create_admin_command_assigns_admin_role_and_redirects_to_admin_space(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Nom complet', 'Premier Admin')
            ->expectsQuestion('Email', 'admin@example.test')
            ->expectsQuestion('Mot de passe (minimum 8 caractères)', 'secure-password')
            ->expectsQuestion('Confirmer le mot de passe', 'secure-password')
            ->assertSuccessful();

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();

        $this->assertSame('admin', $admin->role->value);
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('secure-password', $admin->password));

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'secure-password',
        ])->assertRedirect(route('admin.index'));
    }

    public function test_inactive_user_cannot_login_or_access_a_protected_space(): void
    {
        $user = User::factory()->create(['role' => 'exposant', 'is_active' => false]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->actingAs($user)->get(route('exposant.index'))->assertRedirect(route('login'));
        $this->assertGuest();

        $this->actingAs($user)->get(route('login'))->assertOk();
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        $email = 'rate-limit@example.test';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => $email,
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), [
            'email' => $email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors([
            'email' => 'Tjarbti bzaf. 3awed men be3d chwya.',
        ]);
    }

    public function test_role_cannot_access_another_role_space(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);

        $this->actingAs($commercial)->get(route('admin.index'))->assertForbidden();
        $this->actingAs($commercial)->get(route('commercial.index'))->assertOk();
    }

    public function test_password_reset_link_is_only_sent_to_an_active_account(): void
    {
        Notification::fake();
        $activeUser = User::factory()->create();
        $inactiveUser = User::factory()->create(['is_active' => false]);

        $this->post(route('password.email'), ['email' => $activeUser->email])->assertSessionHas('status');
        Notification::assertSentTo($activeUser, ResetPassword::class);

        $this->post(route('password.email'), ['email' => $inactiveUser->email])->assertSessionHas('status');
        Notification::assertNotSentTo($inactiveUser, ResetPassword::class);
    }

    public function test_active_user_can_reset_password_with_a_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-secret-password', $user->fresh()->password));
    }

    public function test_public_registration_does_not_exist(): void
    {
        $this->get('/register')->assertNotFound();
    }


public function test_admin_can_list_filter_and_view_all_exhibitors(): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $commercial = User::factory()->create(['role' => 'commercial']);

    $exhibitor = $commercial->exhibitors()->create([
        'company_name' => 'Atlas Froid',
        'contact_name' => 'Sara Alami',
        'email' => 'atlas@example.test',
        'phone' => '+212600000000',
        'stand_number' => 'A12',
        'area_sqm' => 24,
        'authorized_badges' => 3,
    ]);

    $exhibitor->badges()->create([
        'first_name' => 'Sara',
        'last_name' => 'Alami',
        'function' => 'Directrice',
        'email' => 'sara@example.test',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.exposants.index'))
        ->assertOk()
        ->assertSee('Atlas Froid')
        ->assertSee('Sara Alami');

    $this->get(route('admin.exposants.index', [
        'search' => 'Atlas',
        'commercial_id' => $commercial->id,
        'status' => 'partial',
    ]))
        ->assertOk()
        ->assertSee('Atlas Froid');

    $this->get(route('admin.exposants.index', [
        'status' => 'complete',
    ]))
        ->assertOk()
        ->assertDontSee('Atlas Froid');

    $this->get(route('admin.exposants.show', $exhibitor))
        ->assertOk()
        ->assertSee('Atlas Froid')
       ->assertSee('Sara Alami')
->assertSee('Badges autorisés')
->assertSee('Badges enregistrés');

    $this->actingAs($commercial)
        ->get(route('admin.exposants.index'))
        ->assertForbidden();

    $this->get(route('admin.exposants.show', $exhibitor))
        ->assertForbidden();
}


public function test_admin_can_manage_equipment_catalog(): void
{
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $commercial = User::factory()->create(['role' => 'commercial']);

    $payload = [
        'name' => 'Table ronde',
        'category' => 'Mobilier',
        'description' => 'Table pour stand exposant.',
        'price' => '250.00',
        'quantity_available' => 10,
        'is_active' => '1',
    ];

    $this->actingAs($admin)
        ->post(route('admin.equipements.store'), [
            ...$payload,
            'image' => UploadedFile::fake()->image('table.png'),
        ])
        ->assertRedirect(route('admin.equipements.index'));

    $item = Equipment::query()->firstOrFail();

    $this->assertSame('Table ronde', $item->name);
    $this->assertSame('250.00', $item->price);
    $this->assertTrue($item->is_active);
    Storage::disk('public')->assertExists($item->image_path);

    $this->get(route('admin.equipements.index', [
        'search' => 'Table',
        'category' => 'Mobilier',
        'status' => 'active',
    ]))
        ->assertOk()
        ->assertSee('Table ronde');

    $this->put(route('admin.equipements.update', $item), [
        ...$payload,
        'name' => 'Table VIP',
        'price' => '350.00',
    ])->assertRedirect(route('admin.equipements.index'));

    $this->assertDatabaseHas('equipment', [
        'id' => $item->id,
        'name' => 'Table VIP',
        'price' => '350.00',
    ]);

    $this->patch(route('admin.equipements.toggle', $item))
        ->assertRedirect();

    $this->assertFalse($item->fresh()->is_active);

    $this->actingAs($commercial)
        ->get(route('admin.equipements.index'))
        ->assertForbidden();

    $this->post(route('admin.equipements.store'), $payload)
        ->assertForbidden();
}


public function test_admin_can_approve_equipment_request_and_update_stock(): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $commercial = User::factory()->create(['role' => 'commercial']);

    $exhibitor = $commercial->exhibitors()->create([
        'company_name' => 'Atlas Froid',
        'contact_name' => 'Sara Alami',
        'email' => 'atlas@example.test',
        'phone' => '+212600000000',
        'stand_number' => 'A12',
        'area_sqm' => 24,
        'authorized_badges' => 3,
    ]);

    $equipment = Equipment::create([
        'name' => 'Table ronde',
        'category' => 'Mobilier',
        'price' => '250.00',
        'quantity_available' => 10,
        'is_active' => true,
    ]);

    $order = EquipmentRequest::create([
        'exhibitor_id' => $exhibitor->id,
        'status' => 'pending',
        'total_amount' => '500.00',
    ]);

    $order->items()->create([
        'equipment_id' => $equipment->id,
        'equipment_name' => $equipment->name,
        'quantity' => 2,
        'unit_price' => '250.00',
        'subtotal' => '500.00',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.demandes.index'))
        ->assertOk()
        ->assertSee('Atlas Froid');

    $this->get(route('admin.demandes.show', $order))
        ->assertOk()
        ->assertSee('Table ronde')
        ->assertSee('500,00');

    $this->patch(route('admin.demandes.approve', $order))
        ->assertRedirect(route('admin.demandes.show', $order));

    $this->assertSame('approved', $order->fresh()->status);
    $this->assertSame(8, $equipment->fresh()->quantity_available);
    $this->assertSame($admin->id, $order->fresh()->reviewed_by);

    // Interdire une deuxième validation.
    $this->patch(route('admin.demandes.approve', $order))
        ->assertSessionHasErrors('status');

    $this->assertSame(8, $equipment->fresh()->quantity_available);

    // Un commercial ne peut pas consulter ces demandes.
    $this->actingAs($commercial)
        ->get(route('admin.demandes.index'))
        ->assertForbidden();

    $this->patch(route('admin.demandes.approve', $order))
        ->assertForbidden();
}

public function test_admin_can_reject_equipment_request_without_changing_stock(): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $commercial = User::factory()->create(['role' => 'commercial']);

    $exhibitor = $commercial->exhibitors()->create([
        'company_name' => 'Froid Test',
        'contact_name' => 'Contact Test',
        'email' => 'froid@example.test',
        'phone' => '+212600000001',
        'stand_number' => 'B12',
        'area_sqm' => 12,
        'authorized_badges' => 2,
    ]);

    $equipment = Equipment::create([
        'name' => 'Chaise',
        'category' => 'Mobilier',
        'price' => '80.00',
        'quantity_available' => 20,
        'is_active' => true,
    ]);

    $order = EquipmentRequest::create([
        'exhibitor_id' => $exhibitor->id,
        'total_amount' => '160.00',
    ]);

    $order->items()->create([
        'equipment_id' => $equipment->id,
        'equipment_name' => 'Chaise',
        'quantity' => 2,
        'unit_price' => '80.00',
        'subtotal' => '160.00',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.demandes.reject', $order), [
            'rejection_reason' => 'Matériel indisponible.',
        ])
        ->assertRedirect(route('admin.demandes.show', $order));

    $this->assertSame('rejected', $order->fresh()->status);
    $this->assertSame(20, $equipment->fresh()->quantity_available);
    $this->assertSame(
        'Matériel indisponible.',
        $order->fresh()->rejection_reason
    );
}

}
