<?php

namespace Tests\Feature;

use App\Models\SalonPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SlotFixtures;
use Tests\TestCase;

class SalonPlanTest extends TestCase
{
    use RefreshDatabase, SlotFixtures;

    private function pdf(string $name = 'plan.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n% Salon plan test\n");
    }

    public function test_commercial_can_publish_and_replace_the_one_shared_pdf_for_all_commercials_and_exhibitors(): void
    {
        Storage::fake('local');
        $firstCommercial = $this->commercial();
        $secondCommercial = $this->commercial();
        $exhibitor = $this->exhibitor('ClimaTech');

        $this->actingAs($firstCommercial)
            ->get(route('commercial.salon-plan.index'))
            ->assertOk()
            ->assertSee('Le plan du salon sera disponible prochainement.');

        $this->actingAs($exhibitor->user)
            ->get(route('exhibitor.salon-plan.index'))
            ->assertOk()
            ->assertSee('Le plan du salon sera disponible prochainement.');

        $this->actingAs($firstCommercial)
            ->post(route('commercial.salon-plan.replace'), ['document' => $this->pdf('plan-initial.pdf')])
            ->assertRedirect(route('commercial.salon-plan.index'));

        $first = SalonPlan::query()->findOrFail(1);
        Storage::disk('local')->assertExists($first->file_path);
        $this->assertSame('plan-initial.pdf', $first->original_name);
        $this->assertSame($firstCommercial->id, $first->updated_by);

        $this->actingAs($secondCommercial)
            ->get(route('commercial.salon-plan.index'))
            ->assertOk()
            ->assertSee('plan-initial.pdf')
            ->assertSee(route('commercial.salon-plan.preview'))
            ->assertSee('data-pdf-action="zoom-in"', false)
            ->assertSee('data-pdf-action="zoom-out"', false)
            ->assertSee('data-pdf-action="fit-width"', false)
            ->assertSee('data-pdf-action="fit-page"', false)
            ->assertSee('data-pdf-action="fullscreen"', false)
            ->assertSee('Télécharger le PDF original');

        $this->actingAs($exhibitor->user)
            ->get(route('exhibitor.salon-plan.index'))
            ->assertOk()
            ->assertSee('plan-initial.pdf')
            ->assertSee(route('exhibitor.salon-plan.preview'))
            ->assertSee('data-pdf-action="zoom-in"', false)
            ->assertSee('data-pdf-action="fit-width"', false)
            ->assertSee('data-pdf-action="fit-page"', false)
            ->assertSee('Télécharger le PDF original');

        $oldPath = $first->file_path;
        $this->actingAs($secondCommercial)
            ->post(route('commercial.salon-plan.replace'), ['document' => $this->pdf('plan-updated.pdf')])
            ->assertRedirect(route('commercial.salon-plan.index'));

        $latest = SalonPlan::query()->findOrFail(1);
        $this->assertSame('plan-updated.pdf', $latest->original_name);
        $this->assertSame($secondCommercial->id, $latest->updated_by);
        $this->assertNotSame($oldPath, $latest->file_path);
        Storage::disk('local')->assertExists($latest->file_path);
        Storage::disk('local')->assertMissing($oldPath);
        $this->actingAs($exhibitor->user)
            ->get(route('exhibitor.salon-plan.index'))
            ->assertSee('plan-updated.pdf');
    }

    public function test_admin_can_publish_replace_consult_download_and_delete_the_shared_pdf(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $commercial = $this->commercial();
        $exhibitor = $this->exhibitor('ClimaTech');

        $this->actingAs($admin)
            ->get(route('admin.salon-plan.index'))
            ->assertOk()
            ->assertSee('Plan du Salon — REFRIGAIR EXPO 2027')
            ->assertSee('Le plan du salon sera disponible prochainement.');

        $this->actingAs($admin)
            ->post(route('admin.salon-plan.replace'), ['document' => $this->pdf('admin-initial.pdf')])
            ->assertRedirect(route('admin.salon-plan.index'));

        $first = SalonPlan::query()->findOrFail(1);
        $firstPath = $first->file_path;
        $this->assertSame(1, SalonPlan::query()->count());
        $this->assertSame('admin-initial.pdf', $first->original_name);
        $this->assertSame($admin->id, $first->updated_by);
        Storage::disk('local')->assertExists($firstPath);

        $this->actingAs($admin)
            ->get(route('admin.salon-plan.index'))
            ->assertOk()
            ->assertSee('admin-initial.pdf')
            ->assertSee('data-pdf-action="zoom-in"', false)
            ->assertSee('data-pdf-action="fit-width"', false)
            ->assertSee('data-pdf-action="fit-page"', false)
            ->assertSee('data-pdf-action="fullscreen"', false);
        $this->actingAs($admin)
            ->get(route('admin.salon-plan.download'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($admin)
            ->post(route('admin.salon-plan.replace'), ['document' => $this->pdf('admin-replacement.pdf')])
            ->assertRedirect(route('admin.salon-plan.index'));

        $replacement = SalonPlan::query()->findOrFail(1);
        $this->assertSame('admin-replacement.pdf', $replacement->original_name);
        $this->assertNotSame($firstPath, $replacement->file_path);
        Storage::disk('local')->assertExists($replacement->file_path);
        Storage::disk('local')->assertMissing($firstPath);

        $this->actingAs($commercial)
            ->get(route('commercial.salon-plan.index'))
            ->assertOk()
            ->assertSee('admin-replacement.pdf');
        $this->actingAs($exhibitor->user)
            ->get(route('exhibitor.salon-plan.index'))
            ->assertOk()
            ->assertSee('admin-replacement.pdf');

        $this->actingAs($admin)
            ->delete(route('admin.salon-plan.destroy'))
            ->assertRedirect(route('admin.salon-plan.index'))
            ->assertSessionHas('status');

        $this->assertNull(SalonPlan::query()->findOrFail(1)->file_path);
        $this->assertNull(SalonPlan::query()->findOrFail(1)->original_name);
        Storage::disk('local')->assertMissing($replacement->file_path);
        $this->actingAs($commercial)->get(route('commercial.salon-plan.index'))
            ->assertOk()->assertSee('Le plan du salon sera disponible prochainement.');
        $this->actingAs($exhibitor->user)->get(route('exhibitor.salon-plan.index'))
            ->assertOk()->assertSee('Le plan du salon sera disponible prochainement.');
    }

    public function test_only_admin_can_manage_the_admin_plan_page_and_delete_the_shared_pdf(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $commercial = $this->commercial();
        $exhibitor = $this->exhibitor('ClimaTech');

        $this->get(route('admin.salon-plan.index'))->assertRedirect(route('login'));
        $this->get(route('admin.salon-plan.preview'))->assertRedirect(route('login'));
        $this->get(route('admin.salon-plan.download'))->assertRedirect(route('login'));
        $this->actingAs($commercial)->get(route('admin.salon-plan.index'))->assertForbidden();
        $this->actingAs($exhibitor->user)->get(route('admin.salon-plan.index'))->assertForbidden();

        $this->actingAs($commercial)
            ->delete(route('admin.salon-plan.destroy'))
            ->assertForbidden();
        $this->actingAs($exhibitor->user)
            ->post(route('admin.salon-plan.replace'), ['document' => $this->pdf()])
            ->assertForbidden();

        $this->assertSame(1, SalonPlan::query()->count());
        $this->assertNull(SalonPlan::query()->findOrFail(1)->file_path);
        $this->actingAs($admin)->get(route('admin.salon-plan.preview'))->assertNotFound();
    }

    public function test_commercial_and_exhibitor_can_preview_and_download_private_pdf_with_correct_headers(): void
    {
        Storage::fake('local');
        $commercial = $this->commercial();
        $exhibitor = $this->exhibitor('ClimaTech');
        $this->actingAs($commercial)->post(route('commercial.salon-plan.replace'), ['document' => $this->pdf()]);

        foreach ([
            [$commercial, 'commercial.salon-plan.preview', 'commercial.salon-plan.download'],
            [$exhibitor->user, 'exhibitor.salon-plan.preview', 'exhibitor.salon-plan.download'],
        ] as [$user, $previewRoute, $downloadRoute]) {
            $this->actingAs($user)
                ->get(route($previewRoute))
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf')
                ->assertHeader('content-disposition', 'inline; filename=plan-du-salon.pdf')
                ->assertHeader('cache-control', 'must-revalidate, no-cache, no-store, private');

            $download = $this->actingAs($user)->get(route($downloadRoute))
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf');
            $this->assertStringContainsString('attachment;', $download->headers->get('content-disposition'));
            $this->assertStringStartsWith('%PDF-', $download->streamedContent());
        }
    }

    public function test_plan_routes_require_authentication_and_only_commercials_can_upload(): void
    {
        Storage::fake('local');
        $commercial = $this->commercial();
        $exhibitor = $this->exhibitor('ClimaTech');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $technical = User::factory()->create(['role' => 'responsable_technique', 'is_active' => true]);

        $this->get(route('commercial.salon-plan.index'))->assertRedirect(route('login'));
        $this->get(route('exhibitor.salon-plan.index'))->assertRedirect(route('login'));
        $this->get(route('commercial.salon-plan.preview'))->assertRedirect(route('login'));
        $this->get(route('exhibitor.salon-plan.download'))->assertRedirect(route('login'));

        $this->actingAs($exhibitor->user)
            ->post(route('commercial.salon-plan.replace'), ['document' => $this->pdf()])
            ->assertForbidden();
        $this->actingAs($admin)->get(route('commercial.salon-plan.index'))->assertForbidden();
        $this->actingAs($technical)->get(route('exhibitor.salon-plan.index'))->assertForbidden();
        $this->actingAs($commercial)->get(route('exhibitor.salon-plan.index'))->assertForbidden();
    }

    public function test_invalid_pdf_is_rejected_and_does_not_replace_the_active_document(): void
    {
        Storage::fake('local');
        $commercial = $this->commercial();

        $this->actingAs($commercial)
            ->post(route('commercial.salon-plan.replace'), ['document' => $this->pdf('plan.pdf')])
            ->assertRedirect(route('commercial.salon-plan.index'));
        $oldPath = SalonPlan::query()->findOrFail(1)->file_path;

        $this->actingAs($commercial)
            ->from(route('commercial.salon-plan.index'))
            ->post(route('commercial.salon-plan.replace'), [
                'document' => UploadedFile::fake()->createWithContent('fake.pdf', 'This is not a PDF.'),
            ])
            ->assertRedirect(route('commercial.salon-plan.index'))
            ->assertSessionHasErrors('document');

        $this->assertSame($oldPath, SalonPlan::query()->findOrFail(1)->file_path);
        Storage::disk('local')->assertExists($oldPath);

        $this->actingAs($commercial)
            ->from(route('commercial.salon-plan.index'))
            ->post(route('commercial.salon-plan.replace'), [
                'document' => UploadedFile::fake()->create('too-large.pdf', 20481, 'application/pdf'),
            ])
            ->assertSessionHasErrors('document');
        $this->assertSame($oldPath, SalonPlan::query()->findOrFail(1)->file_path);

        $disk = Storage::disk('local');
        $failingDisk = \Mockery::mock($disk)->makePartial();
        $failingDisk->shouldReceive('putFileAs')->once()->andReturn(false);
        Storage::shouldReceive('disk')->with('local')->andReturn($failingDisk);

        $this->actingAs($commercial)
            ->from(route('commercial.salon-plan.index'))
            ->post(route('commercial.salon-plan.replace'), ['document' => $this->pdf('cannot-store.pdf')])
            ->assertSessionHasErrors('document');

        $this->assertSame($oldPath, SalonPlan::query()->findOrFail(1)->file_path);
        $disk->assertExists($oldPath);
    }
}
