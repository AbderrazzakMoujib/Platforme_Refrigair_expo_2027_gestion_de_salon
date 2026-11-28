<?php

namespace App\Http\Controllers;

use App\Models\ExhibitorCatalogProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Str;

class ExhibitorCatalogProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $exhibitor = $user->exhibitorProfile()->with('catalogProfile', 'commercial')->firstOrFail();
        $profile = $exhibitor->catalogProfile;
        return view('exhibitor.catalog-profile.edit', compact('user', 'exhibitor', 'profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $exhibitor = $request->user()->exhibitorProfile()->with('catalogProfile')->firstOrFail();
        $validated = $request->validate([
            'company_description' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'action' => ['required', 'in:draft,complete'],
        ]);

        if ($validated['action'] === 'complete' && (blank($validated['company_description'] ?? null) || blank($validated['email'] ?? null))) {
            return back()->withInput()->withErrors(['action' => 'Pour compléter la fiche, renseignez la description et l’e-mail professionnel.']);
        }

        $profile = $exhibitor->catalogProfile ?? new ExhibitorCatalogProfile(['exhibitor_id' => $exhibitor->id]);
        $oldPath = $profile->logo_path;
        $newPath = null;
        $disk = Storage::disk('local');
        try {
            if ($request->hasFile('logo')) {
                $extension = strtolower($request->file('logo')->extension());
                $newPath = $request->file('logo')->storeAs('exhibitor-catalog/logos/'.$exhibitor->id, Str::uuid().'.'.$extension, 'local');
                if (!$newPath) throw new \RuntimeException('Le logo n’a pas pu être enregistré.');
                $profile->logo_path = $newPath;
            } elseif (!$profile->exists && !$request->boolean('remove_logo') && $exhibitor->logo && Storage::disk('public')->exists($exhibitor->logo)) {
                $extension = strtolower(pathinfo($exhibitor->logo, PATHINFO_EXTENSION));
                $newPath = 'exhibitor-catalog/logos/'.$exhibitor->id.'/'.Str::uuid().'.'.$extension;
                if (!$disk->put($newPath, Storage::disk('public')->get($exhibitor->logo))) throw new \RuntimeException('Le logo existant n’a pas pu être copié.');
                $profile->logo_path = $newPath;
            }

            if (($validated['remove_logo'] ?? false) && !$request->hasFile('logo')) {
                $profile->logo_path = null;
            }
            $profile->fill([
                'company_description' => $validated['company_description'] ?? null,
                'website' => $validated['website'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['action'] === 'complete' ? 'completed' : 'draft',
                'completed_at' => $validated['action'] === 'complete' ? ($profile->completed_at ?? now()) : null,
            ]);
            DB::transaction(fn () => $profile->save());
        } catch (\Throwable $exception) {
            if ($newPath) $disk->delete($newPath);
            throw $exception;
        }

        if ($oldPath && $oldPath !== $profile->logo_path && !$exhibitor->catalogProfile()->where('logo_path', $oldPath)->exists()) {
            $disk->delete($oldPath);
        }
        return redirect()->route('exhibitor.catalog-profile.edit')->with('status', 'Votre fiche catalogue a été enregistrée.');
    }

    public function logo(Request $request)
    {
        $exhibitor = $request->user()->exhibitorProfile()->with('catalogProfile')->firstOrFail();
        abort_unless($exhibitor->catalogProfile?->logo_path && Storage::disk('local')->exists($exhibitor->catalogProfile->logo_path), 404);
        return Storage::disk('local')->response($exhibitor->catalogProfile->logo_path);
    }
}
