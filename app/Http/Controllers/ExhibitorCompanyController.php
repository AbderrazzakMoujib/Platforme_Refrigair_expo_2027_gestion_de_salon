<?php

namespace App\Http\Controllers;

use App\Models\Salon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExhibitorCompanyController extends Controller
{
    private function exhibitor(Request $request)
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
            ? min(100, (int) round(
                $registered / $authorized * 100
            ))
            : 0;

        // Informations générales du prochain salon.
        // Pas encore de relation directe exposant/salon.
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

    public function update(Request $request): RedirectResponse
    {
        $exhibitor = $this->exhibitor($request);

        $validated = $request->validate([
            'contact_name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:40',
            ],
        ]);

        // Champs volontairement autorisés :
        // aucune modification du stand, du quota,
        // du commercial ou de l'identité société.
        $exhibitor->contact_name = $validated['contact_name'];
        $exhibitor->email = $validated['email'];
        $exhibitor->phone = $validated['phone'] ?? null;

        $exhibitor->save();

        return redirect()
            ->route('exhibitor.company.index')
            ->with(
                'status',
                'Coordonnées mises à jour avec succès.'
            );
    }
}
