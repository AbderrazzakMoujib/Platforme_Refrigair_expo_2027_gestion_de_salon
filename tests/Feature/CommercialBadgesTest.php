<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Exhibitor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercialBadgesTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    private function exhibitorFor(User $commercial, string $company, int $quota, int $badges = 0): Exhibitor
    {
        $this->sequence++;

        $exhibitor = $commercial->exhibitors()->create([
            'company_name' => $company,
            'contact_name' => 'Contact '.$company,
            'email' => 'contact'.$this->sequence.'@badges.test',
            'phone' => '+212600000000',
            'stand_number' => 'B'.$this->sequence,
            'area_sqm' => 12,
            'authorized_badges' => $quota,
        ]);

        for ($i = 0; $i < $badges; $i++) {
            $this->badgeFor($exhibitor, "Badge{$this->sequence}x{$i}");
        }

        return $exhibitor;
    }

    private function badgeFor(Exhibitor $exhibitor, string $lastName, string $status = 'active'): Badge
    {
        return $exhibitor->badges()->create([
            'first_name' => 'Sara',
            'last_name' => $lastName,
            'function' => 'Technicien',
            'email' => strtolower($lastName).'@badges.test',
            'status' => $status,
        ]);
    }

    public function test_index_shows_scoped_statistics_and_exhibitors(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $other = User::factory()->create(['role' => 'commercial']);

        $mine = $this->exhibitorFor($commercial, 'ClimaTech', 4, 2);
        $this->exhibitorFor($commercial, 'FroidService', 6, 1);
        $this->exhibitorFor($other, 'Concurrent SA', 50, 5);

        // Un badge imprimé
        $this->actingAs($commercial)->post(route('commercial.badges.print', $mine->badges()->first()))->assertOk();

        $this->get(route('commercial.badges.index'))
            ->assertOk()
            ->assertViewHas('totalAuthorized', 10)
            ->assertViewHas('totalBadges', 3)
            ->assertViewHas('printedBadges', 1)
            ->assertViewHas('remainingBadges', 7)
            ->assertViewHas('quotaUsage', 30.0)
            ->assertSee('ClimaTech')
            ->assertSee('FroidService')
            ->assertDontSee('Concurrent SA')
            ->assertSee(route('commercial.badges.export'));
    }

    public function test_exhibitors_can_be_filtered_by_badge_status(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $this->exhibitorFor($commercial, 'Complet SA', 1, 1);
        $this->exhibitorFor($commercial, 'Partiel SA', 3, 1);
        $this->exhibitorFor($commercial, 'Attente SA', 2, 0);
        $this->exhibitorFor($commercial, 'Zero Quota SA', 0, 0);

        $expectations = [
            'complete' => 'Complet SA',
            'partial' => 'Partiel SA',
            'pending' => 'Attente SA',
            'no_quota' => 'Zero Quota SA',
        ];

        foreach ($expectations as $status => $company) {
            $names = $this->actingAs($commercial)
                ->get(route('commercial.badges.index', ['status' => $status]))
                ->assertOk()
                ->viewData('exhibitors')
                ->pluck('company_name')
                ->all();

            $this->assertSame([$company], $names, "Filtre {$status}");
        }
    }

    public function test_badges_and_history_tabs_only_show_own_data(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $other = User::factory()->create(['role' => 'commercial']);

        $mine = $this->exhibitorFor($commercial, 'ClimaTech', 3);
        $badge = $this->badgeFor($mine, 'Amrani');
        $foreign = $this->badgeFor($this->exhibitorFor($other, 'Concurrent', 3), 'Etranger');

        $this->actingAs($commercial)->post(route('commercial.badges.print', $badge))->assertOk();
        $this->actingAs($other)->post(route('commercial.badges.print', $foreign))->assertOk();

        $this->actingAs($commercial)->get(route('commercial.badges.index', ['tab' => 'badges', 'search' => 'Amrani']))
            ->assertOk()
            ->assertSee('Sara Amrani')
            ->assertDontSee('Etranger');

        $this->get(route('commercial.badges.index', ['tab' => 'history']))
            ->assertOk()
            ->assertSee('Badge imprimé')
            ->assertSee('Sara Amrani')
            ->assertDontSee('Sara Etranger');
    }

    public function test_printing_logs_activity_and_is_restricted(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $other = User::factory()->create(['role' => 'commercial']);

        $exhibitor = $this->exhibitorFor($commercial, 'ClimaTech', 5);
        $active = $this->badgeFor($exhibitor, 'Actif');
        $inactive = $this->badgeFor($exhibitor, 'Inactif', 'inactive');
        $foreign = $this->badgeFor($this->exhibitorFor($other, 'Concurrent', 3), 'Autre');

        $this->actingAs($commercial)->post(route('commercial.badges.print', $active))
            ->assertOk()
            ->assertSee($active->reference)
            ->assertSee('data:image/svg+xml', false);

        $this->assertDatabaseHas('badge_activity_logs', [
            'badge_id' => $active->id, 'user_id' => $commercial->id, 'action' => 'printed',
        ]);

        $this->post(route('commercial.badges.print', $inactive))->assertStatus(422);
        $this->post(route('commercial.badges.print', $foreign))->assertNotFound();
        $this->get(route('commercial.badges.index'))->assertOk();
        $this->assertDatabaseCount('badge_activity_logs', 1);
    }

    public function test_bulk_print_only_includes_active_badges_of_own_exhibitor(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $other = User::factory()->create(['role' => 'commercial']);

        $exhibitor = $this->exhibitorFor($commercial, 'ClimaTech', 5);
        $this->badgeFor($exhibitor, 'Un');
        $this->badgeFor($exhibitor, 'Deux');
        $this->badgeFor($exhibitor, 'Off', 'inactive');
        $foreign = $this->exhibitorFor($other, 'Concurrent', 3, 1);

        $response = $this->actingAs($commercial)->post(route('commercial.badges.print-exhibitor', $exhibitor))->assertOk();
        $this->assertCount(2, $response->viewData('items'));
        $this->assertDatabaseCount('badge_activity_logs', 2);

        $this->post(route('commercial.badges.print-exhibitor', $foreign))->assertNotFound();

        $empty = $this->exhibitorFor($commercial, 'Vide', 2);
        $this->post(route('commercial.badges.print-exhibitor', $empty))
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_quota_update_respects_used_badges_and_ownership(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $other = User::factory()->create(['role' => 'commercial']);

        $exhibitor = $this->exhibitorFor($commercial, 'ClimaTech', 5, 3);
        $foreign = $this->exhibitorFor($other, 'Concurrent', 5);

        $this->actingAs($commercial)
            ->from(route('commercial.badges.index'))
            ->patch(route('commercial.badges.quota', $exhibitor), ['authorized_badges' => 8])
            ->assertRedirect(route('commercial.badges.index'));
        $this->assertSame(8, $exhibitor->fresh()->authorized_badges);

        $this->patch(route('commercial.badges.quota', $exhibitor), ['authorized_badges' => 2])
            ->assertSessionHasErrors('authorized_badges');
        $this->assertSame(8, $exhibitor->fresh()->authorized_badges);

        $this->patch(route('commercial.badges.quota', $foreign), ['authorized_badges' => 1])->assertNotFound();
        $this->assertSame(5, $foreign->fresh()->authorized_badges);
    }

    public function test_export_contains_only_own_badges_and_neutralises_formulas(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $other = User::factory()->create(['role' => 'commercial']);

        $exhibitor = $this->exhibitorFor($commercial, 'ClimaTech', 5);
        $this->badgeFor($exhibitor, 'Amrani');
        $exhibitor->badges()->create([
            'first_name' => '=HYPERLINK("x")', 'last_name' => 'Formule',
            'function' => 'Test', 'email' => 'formule@badges.test',
        ]);
        $this->badgeFor($this->exhibitorFor($other, 'Concurrent', 3), 'Etranger');

        $csv = $this->actingAs($commercial)->get(route('commercial.badges.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->streamedContent();

        $this->assertStringContainsString('Amrani', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString('Etranger', $csv);
    }

    public function test_badges_pages_are_forbidden_to_other_roles(): void
    {
        $this->get(route('commercial.badges.index'))->assertRedirect(route('login'));

        foreach (['admin', 'exposant'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get(route('commercial.badges.index'))->assertForbidden();
            $this->actingAs($user)->get(route('commercial.badges.export'))->assertForbidden();
        }
    }

    public function test_sidebar_links_to_badges_page(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);

        $this->actingAs($commercial)->get(route('commercial.index'))
            ->assertOk()
            ->assertSee(route('commercial.badges.index'));
    }
}
