<?php

namespace App\Http\Controllers;

use App\Models\SalonPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Throwable;

class SalonPlanController extends Controller
{
    private const DOCUMENT_ID = 1;

    public function commercial(): View
    {
        return view('commercial.salon-plan.index', $this->viewData());
    }

    public function exhibitor(): View
    {
        return view('exhibitor.salon-plan.index', $this->viewData());
    }

    public function admin(Request $request): View
    {
        $admin = $request->user();
        $initials = collect(preg_split('/\\s+/', trim((string) $admin->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'A';

        return view('admin.salon-plan.index', array_merge($this->viewData(), [
            'admin' => $admin,
            'adminInitials' => $initials,
        ]));
    }

    public function preview(): BinaryFileResponse
    {
        $plan = $this->availablePlan();

        return $this->fileResponse($plan, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    public function download(): BinaryFileResponse
    {
        $plan = $this->availablePlan();

        return $this->fileResponse($plan, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    public function replace(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'document' => [
                'bail',
                'required',
                'file',
                'mimetypes:application/pdf',
                'max:20480',
                static function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value->isValid() || file_get_contents($value->getRealPath(), false, null, 0, 5) !== '%PDF-') {
                        $fail('Le fichier doit être un document PDF valide.');
                    }
                },
            ],
        ], [
            'document.max' => 'Le PDF ne peut pas dépasser 20 Mo.',
            'document.mimetypes' => 'Seuls les fichiers PDF sont acceptés.',
        ]);

        $file = $validated['document'];
        $disk = Storage::disk('local');
        $newPath = $disk->putFileAs('salon-plan', $file, Str::uuid().'.pdf');

        if (! is_string($newPath) || ! $disk->exists($newPath)) {
            return back()->withErrors(['document' => 'Le PDF n’a pas pu être enregistré. Réessayez.']);
        }

        try {
            $oldPath = DB::transaction(function () use ($request, $file, $newPath): ?string {
                $plan = SalonPlan::query()->lockForUpdate()->findOrFail(self::DOCUMENT_ID);
                $previousPath = $plan->file_path;

                $plan->fill([
                    'file_path' => $newPath,
                    'original_name' => Str::limit(basename($file->getClientOriginalName()), 255, ''),
                    'updated_by' => $request->user()->id,
                ])->saveOrFail();

                return $previousPath;
            });
        } catch (Throwable $exception) {
            $disk->delete($newPath);
            report($exception);

            return back()->withErrors(['document' => 'Le remplacement a échoué. Le plan précédent est conservé.']);
        }

        if ($oldPath && $oldPath !== $newPath && ! SalonPlan::query()->where('file_path', $oldPath)->exists()) {
            $disk->delete($oldPath);
        }

        $destination = $request->routeIs('admin.salon-plan.*')
            ? 'admin.salon-plan.index'
            : 'commercial.salon-plan.index';

        return redirect()->route($destination)->with('status', 'Le plan du salon a été mis à jour pour tous les utilisateurs.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $disk = Storage::disk('local');

        try {
            $oldPath = DB::transaction(function () use ($request): ?string {
                $plan = SalonPlan::query()->lockForUpdate()->findOrFail(self::DOCUMENT_ID);
                $previousPath = $plan->file_path;

                $plan->fill([
                    'file_path' => null,
                    'original_name' => null,
                    'updated_by' => $request->user()->id,
                ])->saveOrFail();

                return $previousPath;
            });
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('admin.salon-plan.index')
                ->withErrors(['document' => 'La suppression du plan a échoué. Le document actif est conservé.']);
        }

        $cleanupSucceeded = true;
        if ($oldPath && ! SalonPlan::query()->where('file_path', $oldPath)->exists()) {
            try {
                $cleanupSucceeded = $disk->delete($oldPath);
            } catch (Throwable $exception) {
                report($exception);
                $cleanupSucceeded = false;
            }
        }

        $message = $cleanupSucceeded
            ? 'Le plan actif a été supprimé pour tous les utilisateurs.'
            : 'Le plan actif a été retiré. Le nettoyage du fichier devra être réessayé.';

        return redirect()->route('admin.salon-plan.index')->with('status', $message);
    }

    /** @return array{plan: SalonPlan, documentAvailable: bool} */
    private function viewData(): array
    {
        $plan = SalonPlan::query()->findOrFail(self::DOCUMENT_ID);

        return [
            'plan' => $plan,
            'documentAvailable' => filled($plan->file_path) && Storage::disk('local')->exists($plan->file_path),
        ];
    }

    private function availablePlan(): SalonPlan
    {
        $plan = SalonPlan::query()->findOrFail(self::DOCUMENT_ID);
        abort_unless(filled($plan->file_path) && Storage::disk('local')->exists($plan->file_path), 404);

        return $plan;
    }

    private function fileResponse(SalonPlan $plan, string $disposition): BinaryFileResponse
    {
        $response = new BinaryFileResponse(
            Storage::disk('local')->path($plan->file_path),
            200,
            ['Content-Type' => 'application/pdf'],
            false,
        );
        $response->setContentDisposition($disposition, 'plan-du-salon.pdf');
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');
        $response->headers->addCacheControlDirective('no-cache');
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }

}
