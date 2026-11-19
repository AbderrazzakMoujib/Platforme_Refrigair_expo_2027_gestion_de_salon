<?php

namespace Tests\Feature;

use App\Models\Exhibitor;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommercialDashboardTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    private function exhibitorFor(User $commercial, string $company, int $quota, int $badges = 0, array $overrides = []): Exhibitor
    {
        $this->sequence++;

        $exhibitor = $commercial->exhibitors()->create([
            'company_name' => $company,
            'contact_name' => 'Contact '.$company,
            'email' => 'contact'.$this->sequence.'@dashboard.test',
            'phone' => '+212600000000',
            'stand_number' => 'S'.$this->sequence,
            'area_sqm' => 12,
            'authorized_badges' => $quota,
            ...$overrides,
        ]);

        for ($i = 0; $i < $badges; $i++) {
            $exhibitor->badges()->create([
                'first_name' => 'Badge',
                'last_name' => (string) $i,
                'function' => 'Technicien',
                'email' => "badge{$this->sequence}-{$i}@dashboard.test",
            ]);
        }

        return $exhibitor;
    }

    private function giveAccess(Exhibitor $exhibitor, bool $active = true): void
    {
        $user = User::factory()->create(['role' => 'exposant', 'is_active' => $active]);
        $exhibitor->user()->associate($user)->save();
    }

    public function test_dashboard_computes_badge_statistics_and_exhibitor_statuses(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);

        $complete = $this->exhibitorFor($commercial, 'Complet SA', 2, 2);
        $this->exhibitorFor($commercial, 'Partiel SARL', 4, 1);
        $this->exhibitorFor($commercial, 'Attente SARL', 3, 0);
        $this->exhibitorFor($commercial, 'Sans Quota SA', 0, 0);

        // Un badge archivé (soft delete) ne doit pas être compté.
        $complete->badges()->first()->delete();
        $complete->badges()->create([
            'first_name' => 'Nouveau', 'last_name' => 'Badge',
            'function' => 'Commercial', 'email' => 'new@dashboard.test',
        ]);

        $this->actingAs($commercial)->get(route('commercial.index'))
            ->assertOk()
            ->assertViewHas('exhibitorsCount', 4)
            ->assertViewHas('totalAuthorizedBadges', 9)
            ->assertViewHas('totalRegisteredBadges', 3)
            ->assertViewHas('remainingBadges', 6)
            ->assertViewHas('badgeProgress', 33.3)
            ->assertViewHas('completedExhibitors', 1)
            ->assertViewHas('partialExhibitors', 1)
            ->assertViewHas('pendingExhibitors', 1)
            ->assertViewHas('noQuotaExhibitors', 1)
            ->assertSee('33,3%')
            ->assertSee('Complet')
            ->assertSee('Partiel')
            ->assertSee('En attente')
            ->assertSee('Sans quota');
    }

    public function test_dashboard_only_shows_data_of_the_connected_commercial(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $other = User::factory()->create(['role' => 'commercial']);

        $this->exhibitorFor($commercial, 'Mon Exposant', 2, 1);
        $this->exhibitorFor($other, 'Exposant Concurrent', 50, 10);

        $this->actingAs($commercial)->get(route('commercial.index'))
            ->assertOk()
            ->assertViewHas('exhibitorsCount', 1)
            ->assertViewHas('totalAuthorizedBadges', 2)
            ->assertViewHas('totalRegisteredBadges', 1)
            ->assertSee('Mon Exposant')
            ->assertDontSee('Exposant Concurrent');
    }

    public function test_recent_exhibitors_are_limited_ordered_and_link_to_their_page(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);

        $exhibitors = collect(range(1, 6))->map(function (int $i) use ($commercial) {
            Carbon::setTestNow(now()->addMinutes($i));

            return $this->exhibitorFor($commercial, "Exposant {$i}", 1);
        });
        Carbon::setTestNow();

        $response = $this->actingAs($commercial)->get(route('commercial.index'))->assertOk();

        $recent = $response->viewData('recentExhibitors');
        $this->assertCount(5, $recent);
        $this->assertSame(
            ['Exposant 6', 'Exposant 5', 'Exposant 4', 'Exposant 3', 'Exposant 2'],
            $recent->pluck('company_name')->all()
        );

        $response->assertSee(route('commercial.exhibitors.show', $exhibitors->last()))
            ->assertSee(route('commercial.exhibitors.index'))
            ->assertSee(route('commercial.exhibitors.create'));
    }

    public function test_follow_up_list_contains_pending_and_inactive_access_exhibitors(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);

        $ok = $this->exhibitorFor($commercial, 'Tout Va Bien', 2, 1);
        $this->giveAccess($ok);

        $pending = $this->exhibitorFor($commercial, 'Aucun Badge', 2, 0);
        $this->giveAccess($pending);

        $disabled = $this->exhibitorFor($commercial, 'Acces Coupe', 2, 2);
        $this->giveAccess($disabled, false);

        $this->exhibitorFor($commercial, 'Jamais Connecte', 2, 1);

        $response = $this->actingAs($commercial)->get(route('commercial.index'))
            ->assertOk()
            ->assertViewHas('activeAccessCount', 2)
            ->assertViewHas('withoutAccessCount', 1)
            ->assertViewHas('disabledAccessCount', 1);

        $this->assertEqualsCanonicalizing(
            ['Aucun Badge', 'Acces Coupe', 'Jamais Connecte'],
            $response->viewData('followUpExhibitors')->pluck('company_name')->all()
        );
    }

    public function test_dashboard_handles_empty_state(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);

        $this->actingAs($commercial)->get(route('commercial.index'))
            ->assertOk()
            ->assertViewHas('exhibitorsCount', 0)
            ->assertViewHas('remainingBadges', 0)
            ->assertViewHas('totalTickets', 0)
            ->assertSee('Aucun exposant disponible')
            ->assertSee("Aucune demande d'assistance")
            ->assertSee('Aucune demande sur cette période')
            ->assertSee('Rien à relancer')
            ->assertSee('Aucun quota de badges');
    }

    private function ticketFor(Exhibitor $exhibitor, User $commercial, string $status, string $subject, array $overrides = []): SupportTicket
    {
        $ticket = SupportTicket::query()->create([
            'exhibitor_id' => $exhibitor->id,
            'commercial_id' => $commercial->id,
            'subject' => $subject,
            'category' => 'general',
            'status' => $status,
            'resolved_at' => $status === 'resolved' ? now() : null,
        ]);

        if ($overrides) {
            $ticket->forceFill($overrides)->save();
        }

        return $ticket;
    }

    public function test_dashboard_shows_support_statistics_recent_requests_and_unread_notifications(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $exhibitor = $this->exhibitorFor($commercial, 'ClimaTech', 2);

        $this->ticketFor($exhibitor, $commercial, 'pending', 'Accès au stand');
        $this->ticketFor($exhibitor, $commercial, 'pending', 'Badge supplémentaire');
        $inProgress = $this->ticketFor($exhibitor, $commercial, 'in_progress', 'Options du stand');
        $this->ticketFor($exhibitor, $commercial, 'resolved', 'Logistique');
        $this->ticketFor($exhibitor, $commercial, 'resolved', 'Ancienne demande', ['created_at' => now()->subDays(45)]);

        $commercial->notifications()->create([
            'id' => (string) Str::uuid(), 'type' => 'test', 'data' => ['message' => 'Non lue'],
        ]);
        $commercial->notifications()->create([
            'id' => (string) Str::uuid(), 'type' => 'test', 'data' => ['message' => 'Lue'], 'read_at' => now(),
        ]);

        $response = $this->actingAs($commercial)->get(route('commercial.index'))
            ->assertOk()
            ->assertViewHas('pendingTickets', 2)
            ->assertViewHas('inProgressTickets', 1)
            ->assertViewHas('resolvedTickets', 2)
            ->assertViewHas('activeTickets', 3)
            ->assertViewHas('totalTickets', 5)
            ->assertViewHas('newTicketsToday', 4)
            ->assertViewHas('unreadNotificationsCount', 1)
            ->assertSee("Demandes d'assistance récentes", false)
            ->assertSee('Options du stand')
            ->assertSee(route('commercial.support.show', $inProgress))
            ->assertSee(route('commercial.support.index'))
            ->assertSee(route('commercial.notifications.index'));

        $trend = $response->viewData('ticketTrend');
        $this->assertCount(30, $trend);
        $this->assertSame(4, $trend->sum('count'));
        $this->assertSame(4, $trend->last()['count']);
    }

    public function test_support_statistics_only_include_tickets_of_own_exhibitors(): void
    {
        $commercial = User::factory()->create(['role' => 'commercial']);
        $other = User::factory()->create(['role' => 'commercial']);

        $mine = $this->exhibitorFor($commercial, 'Mon Client', 1);
        $foreign = $this->exhibitorFor($other, 'Client Concurrent', 1);

        $this->ticketFor($mine, $commercial, 'pending', 'Ma demande');
        $this->ticketFor($foreign, $other, 'pending', 'Demande Concurrente');
        // Ticket incohérent : assigné au commercial mais exposant d'un autre commercial.
        $this->ticketFor($foreign, $commercial, 'pending', 'Demande Incoherente');

        $this->actingAs($commercial)->get(route('commercial.index'))
            ->assertOk()
            ->assertViewHas('totalTickets', 1)
            ->assertSee('Ma demande')
            ->assertDontSee('Demande Concurrente')
            ->assertDontSee('Demande Incoherente');
    }

    public function test_dashboard_is_forbidden_to_other_roles_and_guests(): void
    {
        $this->get(route('commercial.index'))->assertRedirect(route('login'));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('commercial.index'))->assertForbidden();

        $exposant = User::factory()->create(['role' => 'exposant']);
        $this->actingAs($exposant)->get(route('commercial.index'))->assertForbidden();
    }
}
