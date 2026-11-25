<?php

namespace Tests\Feature;

use App\Models\Exhibitor;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ExhibitorSupportTest extends TestCase
{
    use RefreshDatabase;

    private function exhibitorFor(User $commercial, User $account, string $company): Exhibitor
    {
        $exhibitor = $commercial->exhibitors()->create([
            'company_name' => $company,
            'contact_name' => 'Contact Test',
            'email' => strtolower(str_replace(' ', '-', $company)).'@example.test',
            'phone' => '0600000000',
            'stand_number' => 'A01',
            'area_sqm' => 12,
            'authorized_badges' => 2,
        ]);

        $account->exhibitorProfile()->save($exhibitor);

        return $exhibitor;
    }

    private function ticket(Exhibitor $exhibitor, User $commercial, string $subject): SupportTicket
    {
        return SupportTicket::query()->create([
            'exhibitor_id' => $exhibitor->id,
            'commercial_id' => $commercial->id,
            'subject' => $subject,
            'category' => 'account',
            'status' => 'pending',
        ]);
    }

    public function test_exhibitor_assistance_lists_only_its_tickets_and_shows_its_commercial(): void
    {
        $commercial = User::factory()->create([
            'role' => 'commercial',
            'name' => 'Commercial Référent',
            'phone' => '+212600000000',
        ]);
        $otherCommercial = User::factory()->create(['role' => 'commercial']);
        $account = User::factory()->create(['role' => 'exposant']);
        $otherAccount = User::factory()->create(['role' => 'exposant']);
        $exhibitor = $this->exhibitorFor($commercial, $account, 'Expo du Client');
        $otherExhibitor = $this->exhibitorFor($otherCommercial, $otherAccount, 'Expo Confidentielle');

        $ownTicket = $this->ticket($exhibitor, $commercial, 'Mon badge est bloqué');
        $ownTicket->messages()->create([
            'user_id' => $account->id,
            'message' => 'Merci de vérifier mon badge.',
        ]);
        $this->ticket($otherExhibitor, $otherCommercial, 'Demande privée concurrente');

        $this->actingAs($account)
            ->get(route('exhibitor.support.index'))
            ->assertOk()
            ->assertSee('Envoyer ma demande')
            ->assertSee('Mon commercial référent')
            ->assertSee('Commercial Référent')
            ->assertSee('Mon badge est bloqué')
            ->assertSee('1 message(s)')
            ->assertDontSee('Demande privée concurrente')
            ->assertDontSee('Expo Confidentielle');
    }

    public function test_guest_is_redirected_and_other_roles_cannot_open_exhibitor_assistance(): void
    {
        $this->get(route('exhibitor.support.index'))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['role' => 'commercial']))
            ->get(route('exhibitor.support.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'responsable_conferences']))
            ->get(route('exhibitor.support.index'))
            ->assertForbidden();
    }

    public function test_exhibitor_can_create_a_ticket_and_open_its_conversation(): void
    {
        Notification::fake();

        $commercial = User::factory()->create(['role' => 'commercial']);
        $account = User::factory()->create(['role' => 'exposant']);
        $exhibitor = $this->exhibitorFor($commercial, $account, 'Expo Création');

        $response = $this->actingAs($account)
            ->post(route('exhibitor.support.store'), [
                'subject' => 'Question sur mon stand',
                'category' => 'stand',
                'message' => 'Pouvez-vous confirmer la configuration de mon stand ?',
            ]);

        $ticket = SupportTicket::query()->firstOrFail();
        $response->assertRedirect(route('exhibitor.support.show', $ticket));

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'exhibitor_id' => $exhibitor->id,
            'commercial_id' => $commercial->id,
            'subject' => 'Question sur mon stand',
        ]);
        $this->assertDatabaseHas('support_messages', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $account->id,
            'message' => 'Pouvez-vous confirmer la configuration de mon stand ?',
        ]);

        $this->get(route('exhibitor.support.show', $ticket))
            ->assertOk()
            ->assertSee('Question sur mon stand')
            ->assertSee('Pouvez-vous confirmer la configuration de mon stand ?');
    }
}
