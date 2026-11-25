<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeToastTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_gets_a_personalized_welcome_toast_once(): void
    {
        $response = $this->loginAndAssertWelcome(
            UserRole::Admin,
            'admin.index',
            'Bienvenue, Élodie !',
            "Connexion réussie. Votre espace d'administration est prêt.",
        );

        $response->assertSee('data-live-notifications', false);
    }

    public function test_commercial_gets_a_personalized_welcome_toast_once(): void
    {
        $this->loginAndAssertWelcome(
            UserRole::Commercial,
            'commercial.index',
            'Bienvenue, Élodie !',
            'Connexion réussie. Retrouvez vos exposants et vos activités.',
        );
    }

    public function test_exhibitor_gets_a_personalized_welcome_toast_once(): void
    {
        $this->loginAndAssertWelcome(
            UserRole::Exposant,
            'exposant.index',
            'Bienvenue, Élodie !',
            'Connexion réussie. Heureux de vous retrouver dans votre espace exposant.',
        );
    }

    public function test_conference_manager_gets_a_personalized_welcome_toast_once(): void
    {
        $this->loginAndAssertWelcome(
            UserRole::ResponsableConferences,
            'responsable-conferences.dashboard',
            'Bienvenue, Élodie !',
            'Connexion réussie. Retrouvez votre planning et vos réservations.',
        );
    }

    public function test_failed_login_does_not_flash_a_welcome_toast(): void
    {
        $user = User::factory()->create(['role' => UserRole::Commercial]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])->assertSessionHasErrors('email');

        $loginPage = $this->get(route('login'))->assertOk();
        $payload = $this->toastPayload($loginPage->getContent());
        $this->assertNotContains('success', array_column($payload, 'type'));
        $this->assertStringNotContainsString('Bienvenue,', json_encode($payload));
    }

    public function test_redirecting_an_already_authenticated_user_does_not_create_a_welcome_toast(): void
    {
        $user = User::factory()->create(['role' => UserRole::ResponsableConferences]);

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect();

        $dashboard = $this->get(route('responsable-conferences.dashboard'))->assertOk();
        $this->assertSame([], $this->toastPayload($dashboard->getContent()));
    }

    public function test_unusual_name_uses_a_safe_fallback_and_dynamic_name_is_not_html(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Exposant,
            'name' => '<script>alert(1)</script>',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('exposant.index'));

        $dashboard = $this->get(route('exposant.index'))->assertOk();
        $payload = $this->toastPayload($dashboard->getContent());
        $this->assertSame('Bienvenue, utilisateur !', $payload[0]['title']);
        $this->assertStringNotContainsString('alert(1)', $payload[0]['title']);
        $this->assertSame(5000, $payload[0]['duration']);
    }

    private function loginAndAssertWelcome(
        UserRole $role,
        string $dashboardRoute,
        string $expectedTitle,
        string $expectedMessage,
    ): \Illuminate\Testing\TestResponse {
        $user = User::factory()->create([
            'name' => 'Élodie Martin',
            'role' => $role,
            'password' => 'correct-password',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect(route($dashboardRoute))
            ->assertSessionHas('welcome_toast', fn (array $toast): bool =>
                $toast['type'] === 'success'
                && $toast['title'] === $expectedTitle
                && $toast['message'] === $expectedMessage
                && $toast['duration'] === 5000
            );

        $dashboard = $this->get(route($dashboardRoute))->assertOk();
        $payload = $this->toastPayload($dashboard->getContent());
        $this->assertSame('success', $payload[0]['type']);
        $this->assertSame($expectedTitle, $payload[0]['title']);
        $this->assertSame($expectedMessage, $payload[0]['message']);
        $this->assertSame(5000, $payload[0]['duration']);

        $refresh = $this->get(route($dashboardRoute))->assertOk();
        $this->assertSame([], $this->toastPayload($refresh->getContent()));

        return $dashboard;
    }

    /** @return array<int, array<string, mixed>> */
    private function toastPayload(string $html): array
    {
        $this->assertMatchesRegularExpression('/data-toast-payload="([A-Za-z0-9+\/=]*)"/', $html);
        preg_match('/data-toast-payload="([A-Za-z0-9+\/=]*)"/', $html, $matches);

        return json_decode(base64_decode($matches[1]), true, flags: JSON_THROW_ON_ERROR);
    }
}
