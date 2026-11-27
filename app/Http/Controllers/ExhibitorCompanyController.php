<?php

namespace App\Http\Controllers;

use App\Models\Exhibitor;
use App\Models\Salon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ExhibitorCompanyController extends Controller
{
    private function exhibitor(Request $request): Exhibitor
    {
        return $request->user()
            ->exhibitorProfile()
            ->firstOrFail();
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $exhibitor = $this->exhibitor($request);
        $exhibitor->load('commercial');

        $authorized = (int) $exhibitor->authorized_badges;
        $registered = $exhibitor->badges()->count();
        $remaining = max(0, $authorized - $registered);
        $progress = $authorized > 0
            ? min(100, (int) round($registered / $authorized * 100))
            : 0;

        $salon = Salon::query()
            ->whereDate('end_date', '>=', today())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('start_date')
            ->first();

        return view('exhibitor.company.index', compact(
            'user',
            'exhibitor',
            'authorized',
            'registered',
            'remaining',
            'progress',
            'salon'
        ));
    }

    public function profile(Request $request): View
    {
        return view('exhibitor.profile.index', [
            'user' => $request->user(),
            'exhibitor' => $this->exhibitor($request),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('profile', [
            'contact_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $disk = Storage::disk('public');
        $newLogo = null;

        if ($request->hasFile('logo')) {
            try {
                // Laravel generates a random filename; the upload is never named by user input.
                $newLogo = $request->file('logo')->store('exhibitors/logos', 'public');
                if (! is_string($newLogo) || $newLogo === '' || ! $disk->exists($newLogo)) {
                    throw new RuntimeException('Logo storage failed.');
                }
            } catch (Throwable $exception) {
                if (is_string($newLogo) && $newLogo !== '') {
                    try {
                        $disk->delete($newLogo);
                    } catch (Throwable $cleanupException) {
                        report($cleanupException);
                    }
                }
                report($exception);

                return back()->withErrors([
                    'logo' => 'Le nouveau logo n’a pas pu être enregistré. L’ancien est conservé.',
                ], 'profile');
            }
        }

        try {
            $previousLogo = DB::transaction(function () use ($request, $validated, $newLogo): ?string {
                $exhibitor = $request->user()
                    ->exhibitorProfile()
                    ->lockForUpdate()
                    ->firstOrFail();
                $previous = $exhibitor->logo;

                // Only contact details and logo are editable from this account.
                $exhibitor->contact_name = $validated['contact_name'];
                $exhibitor->email = $validated['email'];
                $exhibitor->phone = $validated['phone'] ?? null;
                if ($newLogo !== null) {
                    $exhibitor->logo = $newLogo;
                }
                $exhibitor->saveOrFail();

                return $previous;
            });
        } catch (Throwable $exception) {
            if ($newLogo !== null) {
                try {
                    $disk->delete($newLogo);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }
            report($exception);

            return back()->withErrors([
                'contact_name' => 'La mise à jour a échoué. Vos anciennes coordonnées et votre ancien logo sont conservés.',
            ], 'profile');
        }

        if ($newLogo !== null && $previousLogo && $previousLogo !== $newLogo) {
            $isShared = Exhibitor::query()
                ->where('logo', $previousLogo)
                ->whereKeyNot($this->exhibitor($request)->getKey())
                ->exists();

            if (! $isShared) {
                try {
                    $disk->delete($previousLogo);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        }

        return redirect()
            ->route('exhibitor.profile')
            ->with('status', 'Votre profil entreprise a été mis à jour.');
    }
}
