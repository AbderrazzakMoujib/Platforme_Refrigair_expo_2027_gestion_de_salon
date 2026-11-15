<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\CommercialInvitation;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class CommercialInvitationController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $invitation = $this->findValidInvitation($token);

        if (! $invitation) {
            return view('auth.commercial-invitation-invalid');
        }

        return view('auth.accept-commercial-invitation', ['token' => $token, 'email' => $invitation->user->email]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $key = 'commercial-invitation:'.hash('sha256', $token).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['password' => 'Trop de tentatives. Réessaie dans quelques minutes.']);
        }

        $invitation = $this->findValidInvitation($token);
        if (! $invitation) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['password' => 'Lien invalide, expiré ou déjà utilisé. Demande une nouvelle invitation à l’Admin.']);
        }

        $commercial = $invitation->user;
        if (! $commercial->is_active) {
            return back()->withErrors(['password' => 'Ce compte est désactivé. Contacte l’Admin.']);
        }

        $commercial->forceFill(['password' => Hash::make($request->string('password')->toString())])->save();
        $invitation->forceFill(['accepted_at' => now()])->save();
        CommercialInvitation::query()->where('user_id', $commercial->id)->whereNull('accepted_at')->delete();
        event(new PasswordReset($commercial));
        RateLimiter::clear($key);

        return redirect()->route('login')->with('status', 'Accès activé. Tu peux maintenant te connecter.');
    }

    public static function validFor(User $user): ?CommercialInvitation
    {
        return $user->password === null
            ? CommercialInvitation::query()->where('user_id', $user->id)->whereNull('accepted_at')->where('expires_at', '>', now())->latest('id')->first()
            : null;
    }

    public static function statusFor(User $user): string
    {
        if ($user->password !== null) return 'Accès activé';
        $latest = CommercialInvitation::query()->where('user_id', $user->id)->latest('id')->first();
        if (! $latest) return 'Non invitée';
        return $latest->expires_at->isPast() ? 'Invitation expirée' : 'Invitation envoyée';
    }

    private function findValidInvitation(string $token): ?CommercialInvitation
    {
        return CommercialInvitation::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->whereHas('user', fn ($query) => $query->where('role', UserRole::Commercial->value))
            ->first();
    }
}
