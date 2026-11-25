<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $isActiveUser = User::query()
            ->where('email', $request->input('email'))
            ->where('is_active', true)
            ->whereNotNull('password')
            ->exists();

        if ($isActiveUser) {
            Password::sendResetLink($request->only('email'));
        }

        return back()->with('status', 'Ila l-email kayn f compte actif, ghadi twslek lien bach tbeddel mot de passe.');
    }
}
