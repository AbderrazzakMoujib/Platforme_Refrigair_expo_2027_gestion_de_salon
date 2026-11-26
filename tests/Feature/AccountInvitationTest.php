<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Mail\CommercialInvitationMail;
use App\Models\CommercialInvitation;
use App\Models\User;
use App\Services\AccountInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_invitable_role_can_activate_once_without_automatic_login(): void
    {
        foreach ([UserRole::Commercial, UserRole::ResponsableConferences, UserRole::Exposant] as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'password' => null,
                'email_verified_at' => null,
                'is_active' => true,
            ]);
            $issued = app(AccountInvitationService::class)->issueFor($user);
            Mail::fake();
            $this->assertTrue(app(AccountInvitationService::class)->deliver($issued['invitation'], $issued['token']));

            $this->get(route('account-activation.show', $issued['token']))
                ->assertOk()
                ->assertSee($user->email);

            $this->post(route('account-activation.accept', $issued['token']), [
                'password' => 'NewSecurePassword123!',
                'password_confirmation' => 'NewSecurePassword123!',
            ])->assertRedirect(route('login'));

            $user->refresh();
            $this->assertTrue(password_verify('NewSecurePassword123!', $user->password));
            $this->assertNotNull($user->email_verified_at);
            $this->assertNotNull($issued['invitation']->fresh()->accepted_at);
            $this->assertGuest();
            $this->get(route('account-activation.show', $issued['token']))->assertSee('déjà utilisé');
        }
    }

    public function test_technical_manager_can_activate_from_admin_invitation(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::ResponsableTechnique,
            'password' => null,
            'email_verified_at' => null,
            'is_active' => true,
        ]);
        $issued = app(AccountInvitationService::class)->issueFor($user);
        Mail::fake();
        $this->assertTrue(app(AccountInvitationService::class)->deliver($issued['invitation'], $issued['token']));

        $this->get(route('account-activation.show', $issued['token']))->assertOk()->assertSee('Responsable Technique');
        $this->post(route('account-activation.accept', $issued['token']), [
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ])->assertRedirect(route('login'));

        $this->assertTrue(password_verify('NewSecurePassword123!', $user->fresh()->password));
        $this->assertNotNull($issued['invitation']->fresh()->accepted_at);
    }

    public function test_expired_and_revoked_invitations_cannot_activate(): void
    {
        $user = User::factory()->create(['role' => UserRole::Exposant, 'password' => null]);
        $issued = app(AccountInvitationService::class)->issueFor($user);
        $issued['invitation']->update(['delivery_status' => CommercialInvitation::STATUS_SENT, 'sent_at' => now(), 'expires_at' => now()->subMinute()]);

        $this->post(route('account-activation.accept', $issued['token']), [
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ])->assertRedirect(route('account-activation.show', $issued['token']));
        $this->assertNull($user->fresh()->password);

        $issued['invitation']->update(['expires_at' => now()->addHour(), 'delivery_status' => CommercialInvitation::STATUS_REVOKED]);
        $this->get(route('account-activation.show', $issued['token']))->assertSee('révoquée');
    }

    public function test_activation_does_not_allow_admin_role_and_admin_is_unique(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $service = app(AccountInvitationService::class);
        try {
            $service->issueFor($admin);
            $this->fail('Admin role must not be invitable.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }

        $this->artisan('app:create-admin', ['--no-interaction' => true])->assertFailed();
        $this->assertSame(1, User::where('role', UserRole::Admin)->count());
    }

    public function test_mail_contains_activation_link_and_never_a_password(): void
    {
        Mail::fake();
        $user = User::factory()->create(['role' => UserRole::Commercial, 'password' => null]);
        $issued = app(AccountInvitationService::class)->issueFor($user);
        app(AccountInvitationService::class)->deliver($issued['invitation'], $issued['token']);

        Mail::assertSent(CommercialInvitationMail::class, function (CommercialInvitationMail $mail) use ($issued, $user): bool {
            $this->assertStringContainsString($issued['token'], $mail->activationUrl);
            $this->assertStringContainsString($user->email, $mail->render());
            $this->assertStringNotContainsString('NewSecurePassword123!', $mail->render());
            return $mail->user->is($user);
        });
        $this->assertDatabaseMissing('commercial_invitations', ['token_hash' => $issued['token']]);
    }

    public function test_admin_and_commercial_create_invited_accounts_without_initial_passwords(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin)->post(route('admin.commerciaux.store'), [
            'name' => 'Nadia Commercial', 'email' => 'nadia@example.test', 'phone' => '0600000000',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('admin.responsables-conferences.store'), [
            'name' => 'Yassine Responsable', 'email' => 'yassine@example.test',
        ])->assertRedirect();

        $commercial = User::factory()->create(['role' => UserRole::Commercial, 'password' => Hash::make('ExistingSecurePassword!')]);
        $this->actingAs($commercial)->post(route('commercial.exhibitors.store'), [
            'company_name' => 'Atlas Froid', 'contact_name' => 'Samira Atlas',
            'email' => 'samira@example.test', 'phone' => '0600000001',
            'stand_number' => 'A12', 'area_sqm' => 24, 'authorized_badges' => 4,
        ])->assertRedirect();

        foreach (['nadia@example.test', 'yassine@example.test', 'samira@example.test'] as $email) {
            $user = User::where('email', $email)->firstOrFail();
            $this->assertNull($user->password);
            $this->assertTrue($user->is_active);
            $this->assertDatabaseHas('commercial_invitations', [
                'user_id' => $user->id,
                'delivery_status' => CommercialInvitation::STATUS_SENT,
            ]);
        }
        Mail::assertSent(CommercialInvitationMail::class, 3);
    }

    public function test_resent_invitation_revokes_previous_link(): void
    {
        Mail::fake();
        $user = User::factory()->create(['role' => UserRole::Commercial, 'password' => null]);
        $service = app(AccountInvitationService::class);
        $first = $service->issueFor($user);
        $service->deliver($first['invitation'], $first['token']);

        $this->travel(2)->minutes();
        $next = $service->prepareResend($user);
        $this->assertArrayHasKey('invitation', $next);
        $this->assertSame(CommercialInvitation::STATUS_REVOKED, $first['invitation']->fresh()->delivery_status);
        $service->deliver($next['invitation'], $next['token']);

        $this->get(route('account-activation.show', $first['token']))->assertSee('révoquée');
        $this->get(route('account-activation.show', $next['token']))->assertOk();
    }
}
