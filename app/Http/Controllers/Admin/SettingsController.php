<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $admin = $request->user();

        $adminInitials = collect(
            preg_split('/\s+/', trim($admin->name))
        )
            ->filter()
            ->take(2)
            ->map(fn (string $part) =>
                mb_strtoupper(mb_substr($part, 0, 1))
            )
            ->implode('') ?: 'A';

        return view('admin.settings.index', compact(
            'admin',
            'adminInitials'
        ));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = $request->user();

        $validated = $request->validateWithBag('profile', [
            'name' => [
                'required', 'string', 'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($admin->id),
            ],
            'phone' => [
                'nullable', 'string', 'max:40',
            ],
        ]);

        $emailChanged = $admin->email !== $validated['email'];

        $admin->fill($validated);

        if ($emailChanged) {
            $admin->email_verified_at = null;
        }

        $admin->save();

        return redirect()
            ->route('admin.parametres.index')
            ->with('profile_status', 'Profil mis à jour avec succès.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('password', [
            'current_password' => [
                'required',
                'current_password',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
                'different:current_password',
            ],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        $request->session()->regenerate();

        return redirect()
            ->route('admin.parametres.index')
            ->with('password_status', 'Mot de passe modifié avec succès.');
    }
}
