<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\BadgeApiClient;
use App\Models\User;
use App\Services\BadgeQrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BadgeApiTest extends TestCase
{
    use RefreshDatabase;

    private function createClient(string $plainTextToken, array $attributes = []): BadgeApiClient
    {
        return BadgeApiClient::query()->create(array_merge([
            'name' => 'BadgeKiosk test',
            'token_hash' => hash('sha256', $plainTextToken),
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ], $attributes));
    }

    private function createBadge(string $status = 'active'): Badge
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $exhibitor = $commercial->exhibitors()->create([
            'company_name' => 'Atlas Froid',
            'contact_name' => 'Contact Atlas',
            'email' => 'contact@atlas.example.test',
            'phone' => '+212600000000',
            'stand_number' => 'A12',
            'authorized_badges' => 3,
        ]);

        return $exhibitor->badges()->create([
            'first_name' => 'Sara',
            'last_name' => 'Amrani',
            'function' => 'Technicienne',
            'email' => 'sara.private@example.test',
            'phone' => '+212611111111',
            'status' => $status,
        ]);
    }

    public function test_shared_qr_generator_encodes_exactly_the_existing_uuid_for_every_badge(): void
    {
        $badge = $this->createBadge();
        $existingToken = $badge->qr_token;

        $qrCode = app(BadgeQrCodeService::class)->make($badge);

        $this->assertSame($existingToken, $qrCode->getData());
        $this->assertSame($existingToken, $badge->fresh()->qr_token);
        $this->assertNotSame((string) $badge->id, $qrCode->getData());
        $this->assertStringNotContainsString('https://', $qrCode->getData());

        $svg = app(BadgeQrCodeService::class)->svg($badge);
        $this->assertStringContainsString('<svg', $svg);
    }

    public function test_lookup_requires_a_valid_client_token_and_returns_only_minimum_active_badge_data(): void
    {
        $badge = $this->createBadge();
        $token = Str::random(64);
        $client = $this->createClient($token);

        $this->postJson('/api/v1/badges/lookup', ['qr_token' => $badge->qr_token])
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'unauthenticated');

        $this->withToken($token)->postJson('/api/v1/badges/lookup', ['qr_token' => 'not-a-uuid'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('qr_token');

        $this->withToken($token)->postJson('/api/v1/badges/lookup', ['qr_token' => $badge->qr_token])
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'data' => [
                    'qr_token' => $badge->qr_token,
                    'status' => 'active',
                    'active' => true,
                    'first_name' => 'Sara',
                    'last_name' => 'Amrani',
                    'function' => 'Technicienne',
                    'company' => ['name' => 'Atlas Froid', 'stand_number' => 'A12'],
                ],
            ])
            ->assertJsonMissingPath('data.id')
            ->assertJsonMissingPath('data.email')
            ->assertJsonMissingPath('data.phone')
            ->assertJsonMissingPath('data.company.contact_name');

        $this->assertNotSame($token, $client->fresh()->token_hash);
        $this->assertNotNull($client->fresh()->last_used_at);
        $this->assertDatabaseHas('badges', ['id' => $badge->id, 'qr_token' => $badge->qr_token]);
    }

    public function test_lookup_distinguishes_unknown_inactive_and_soft_deleted_badges(): void
    {
        $token = Str::random(64);
        $this->createClient($token);
        $inactive = $this->createBadge('inactive');

        $this->withToken($token)->postJson('/api/v1/badges/lookup', ['qr_token' => (string) Str::uuid()])
            ->assertNotFound()
            ->assertJsonPath('error.code', 'badge_not_found');

        $this->withToken($token)->postJson('/api/v1/badges/lookup', ['qr_token' => $inactive->qr_token])
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'data' => ['qr_token' => $inactive->qr_token, 'status' => 'inactive', 'active' => false],
            ]);

        $inactive->delete();
        $this->withToken($token)->postJson('/api/v1/badges/lookup', ['qr_token' => $inactive->qr_token])
            ->assertNotFound();

        $this->assertSoftDeleted('badges', ['id' => $inactive->id, 'qr_token' => $inactive->qr_token]);
    }

    public function test_revoked_expired_and_non_https_production_clients_are_rejected(): void
    {
        $token = Str::random(64);
        $revoked = $this->createClient($token, ['is_active' => false]);
        $this->withToken($token)->postJson('/api/v1/badges/lookup', ['qr_token' => (string) Str::uuid()])
            ->assertUnauthorized();

        $expiredToken = Str::random(64);
        $this->createClient($expiredToken, ['expires_at' => now()->subMinute()]);
        $this->withToken($expiredToken)->postJson('/api/v1/badges/lookup', ['qr_token' => (string) Str::uuid()])
            ->assertUnauthorized();

        $validProductionToken = Str::random(64);
        $this->createClient($validProductionToken);
        config(['app.env' => 'production']);
        $this->withToken($validProductionToken)->postJson('/api/v1/badges/lookup', ['qr_token' => (string) Str::uuid()])
            ->assertBadRequest()
            ->assertJsonPath('error.code', 'https_required');
        $this->withToken($validProductionToken)
            ->postJson('https://localhost/api/v1/badges/lookup', ['qr_token' => (string) Str::uuid()])
            ->assertNotFound();
        config(['app.env' => 'testing']);

        $this->assertDatabaseHas('badge_api_clients', ['id' => $revoked->id, 'is_active' => false]);
    }

    public function test_server_commands_create_and_revoke_clients_without_storing_plaintext_secrets(): void
    {
        $this->artisan('badge-api:client:create', ['name' => 'BadgeKiosk Android'])
            ->expectsOutputToContain('Client créé')
            ->assertSuccessful();

        $client = BadgeApiClient::query()->firstOrFail();
        $this->assertSame('BadgeKiosk Android', $client->name);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $client->token_hash);
        $this->assertTrue($client->is_active);
        $this->assertTrue($client->expires_at->isFuture());

        $this->artisan('badge-api:client:revoke', ['client' => $client->id])
            ->expectsOutputToContain('Jeton révoqué')
            ->assertSuccessful();

        $this->assertFalse($client->fresh()->is_active);
    }

    public function test_lookup_is_rate_limited_even_before_client_authentication(): void
    {
        for ($attempt = 0; $attempt < 300; $attempt++) {
            $this->postJson('/api/v1/badges/lookup', [])->assertUnauthorized();
        }

        $this->postJson('/api/v1/badges/lookup', [])->assertTooManyRequests();
    }

    public function test_legacy_verification_url_remains_available_and_is_still_role_protected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $badge = $this->createBadge();

        $this->actingAs($admin)
            ->get(route('admin.badges.verify', ['token' => $badge->qr_token]))
            ->assertOk()
            ->assertSee('Badge valide');

        auth()->logout();
        $this->get(route('admin.badges.verify', ['token' => $badge->qr_token]))
            ->assertRedirect(route('login'));

        $this->assertSame(
            '/admin/badges/verify/'.$badge->qr_token,
            route('admin.badges.verify', ['token' => $badge->qr_token], false)
        );
    }

    public function test_exhibitor_qr_endpoint_uses_the_shared_uuid_generator_and_enforces_ownership(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $account = User::factory()->create(['role' => 'exposant']);
        $exhibitor = $commercial->exhibitors()->create([
            'company_name' => 'Atlas QR',
            'contact_name' => 'Contact QR',
            'email' => 'qr@example.test',
            'authorized_badges' => 1,
        ]);
        $account->exhibitorProfile()->save($exhibitor);
        $badge = $exhibitor->badges()->create([
            'first_name' => 'Sara', 'last_name' => 'Amrani', 'function' => 'Technicienne',
            'email' => 'sara.qr@example.test',
        ]);

        $this->actingAs($account)->get(route('exhibitor.badges.qr', $badge))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml')
            ->assertSee('<svg', false);

        $otherAccount = User::factory()->create(['role' => 'exposant']);
        $this->actingAs($otherAccount)->get(route('exhibitor.badges.qr', $badge))->assertForbidden();
    }
}
