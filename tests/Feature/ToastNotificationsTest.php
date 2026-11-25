<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ToastNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_flash_message_is_displayed_once_and_disappears_after_refresh(): void
    {
        Route::middleware('web')->get('/test-toast-success', fn () => redirect()->route('login')->with('success', 'Toast success unique'));

        $this->get('/test-toast-success')->assertRedirect(route('login'));
        $firstLogin = $this->get('/login')->assertOk()->assertSee('global-toast-root');
        $this->assertSame('Toast success unique', $this->toastPayload($firstLogin->getContent())[0]['message']);

        $nextLogin = $this->get('/login')->assertOk();
        $this->assertSame([], $this->toastPayload($nextLogin->getContent()));
    }

    public function test_validation_error_keeps_form_details_and_adds_a_warning_toast(): void
    {
        Route::middleware('web')->get('/test-toast-validation', fn () => redirect()->route('login')
            ->with('status', 'Ne pas afficher un faux succès')
            ->withErrors(['email' => 'Adresse e-mail invalide pour ce test.']));

        $response = $this->followingRedirects()->get('/test-toast-validation')->assertOk();
        $response->assertSee('Adresse e-mail invalide pour ce test.');

        $payload = $this->toastPayload($response->getContent());
        $this->assertSame('warning', $payload[0]['type']);
        $this->assertStringContainsString('Les détails restent affichés', $payload[0]['message']);
        $this->assertCount(1, $payload);
    }

    public function test_access_denied_page_has_a_safe_error_toast(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $response = $this->actingAs($commercial)->get(route('admin.index'))->assertForbidden();
        $payload = $this->toastPayload($response->getContent());

        $this->assertSame('error', $payload[0]['type']);
        $this->assertSame('Accès refusé', $payload[0]['title']);
        $this->assertStringNotContainsString($commercial->email, json_encode($payload));
    }

    public function test_all_four_authenticated_spaces_render_the_same_global_toast_root(): void
    {
        $destinations = [
            ['admin', 'admin.index'],
            ['commercial', 'commercial.index'],
            ['exposant', 'exposant.index'],
            ['responsable_conferences', 'responsable-conferences.dashboard'],
        ];

        foreach ($destinations as [$role, $route]) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get(route($route))->assertOk()->assertSee('id="global-toast-root"', false);
        }
    }

    public function test_dynamic_flash_content_is_escaped_and_internal_secrets_are_redacted(): void
    {
        $unsafeMarkup = '<img src=x onerror=alert(1)>';
        Route::middleware('web')->get('/test-toast-unsafe', fn () => redirect()->route('login')->with('status', $unsafeMarkup));
        $this->get('/test-toast-unsafe')->assertRedirect(route('login'));
        $response = $this->get(route('login'));
        $response->assertDontSee($unsafeMarkup, false);
        $this->assertSame($unsafeMarkup, $this->toastPayload($response->getContent())[0]['message']);

        Route::middleware('web')->get('/test-toast-sensitive', fn () => redirect()->route('login')->with('error', 'Internal exception password=TopSecret123'));
        $this->get('/test-toast-sensitive')->assertRedirect(route('login'));
        $response = $this->get(route('login'));
        $payload = $this->toastPayload($response->getContent());
        $this->assertStringNotContainsString('TopSecret123', json_encode($payload));
        $this->assertStringContainsString('Une erreur est survenue', $payload[0]['message']);
    }

    public function test_business_notification_bell_remains_present_separately(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get(route('admin.index'))->assertOk();

        $response->assertSee('global-toast-root', false)
            ->assertSee('data-live-notifications', false);
    }

    /** @return array<int, array{type: string, title: string, message: string}> */
    private function toastPayload(string $html): array
    {
        $this->assertMatchesRegularExpression('/data-toast-payload="([A-Za-z0-9+\/=]*)"/', $html, 'Toast payload is missing.');
        preg_match('/data-toast-payload="([A-Za-z0-9+\/=]*)"/', $html, $matches);

        return json_decode(base64_decode($matches[1]), true, flags: JSON_THROW_ON_ERROR);
    }
}
