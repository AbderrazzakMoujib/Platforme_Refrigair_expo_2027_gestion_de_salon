<?php

namespace Tests\Feature;

use App\Enums\SlotStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SlotFixtures;
use Tests\TestCase;

class ExhibitorPlanningPdfTest extends TestCase
{
    use RefreshDatabase, SlotFixtures;

    public function test_exhibitor_can_download_the_conference_program_without_other_exhibitors_private_details(): void
    {
        $this->seedSlots();

        $commercial = $this->commercial();
        $ownExhibitor = $this->exhibitor('Mon entreprise', true, $commercial);
        $otherExhibitor = $this->exhibitor('Entreprise confidentielle', true);

        $this->slot('C2-1')->update([
            'status' => SlotStatus::Reserved,
            'exhibitor_id' => $ownExhibitor->id,
            'exhibitor_name' => null,
            'title' => 'Mon intervention',
        ]);
        $this->slot('C2-2')->update([
            'status' => SlotStatus::Reserved,
            'exhibitor_id' => $otherExhibitor->id,
            'exhibitor_name' => null,
            'title' => 'Sujet privé autre exposant',
            'contact_email' => 'secret@example.test',
        ]);

        $response = $this->actingAs($ownExhibitor->user)
            ->get(route('exhibitor.slots.export.pdf', 'fr'))
            ->assertOk();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $response->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
            $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        } else {
            $response->assertSee('Mon entreprise')
                ->assertSee('Mon intervention')
                ->assertDontSee('Entreprise confidentielle')
                ->assertDontSee('Sujet privé autre exposant')
                ->assertDontSee('secret@example.test');
        }
    }

    public function test_exhibitor_planning_page_links_to_the_program_and_guests_are_redirected_to_login(): void
    {
        $this->seedSlots();
        $exhibitor = $this->exhibitor('ClimaTech');

        $this->get(route('exhibitor.slots.export.pdf', 'fr'))
            ->assertRedirect(route('login'));

        $this->actingAs($exhibitor->user)
            ->get(route('exhibitor.slots.index'))
            ->assertOk()
            ->assertSee(route('exhibitor.slots.export.pdf', 'fr'))
            ->assertSee('Programme PDF');

        $this->actingAs($this->commercial())
            ->get(route('exhibitor.slots.export.pdf', 'fr'))
            ->assertForbidden();
    }

    public function test_commercial_planning_pdf_remains_available(): void
    {
        $this->seedSlots();
        $commercial = $this->commercial();

        $this->actingAs($commercial)
            ->get(route('commercial.slots.export.pdf', 'fr'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
