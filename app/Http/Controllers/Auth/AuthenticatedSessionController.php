<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->is_active) {
            return redirect()->route($this->homeRouteFor(Auth::user()->role));
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['email']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Tjarbti bzaf. 3awed men be3d chwya.',
            ]);
        }

        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'Email ola mot de passe ghalat, ola lcompte ma mfa3elch.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $user = Auth::user();
        [$message, $firstName] = $this->welcomeFor($user->role, $user->name);
        $request->session()->flash('welcome_toast', [
            'type' => 'success',
            'title' => "Bienvenue, {$firstName} !",
            'message' => $message,
            'duration' => 5000,
        ]);

        return redirect()->route($this->homeRouteFor($user->role));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function homeRouteFor(UserRole|string $role): string
    {
        $value = $role instanceof UserRole ? $role->value : $role;

        return match ($value) {
            UserRole::Admin->value => 'admin.index',
            UserRole::Commercial->value => 'commercial.index',
            UserRole::ResponsableConferences->value => 'responsable-conferences.dashboard',
            UserRole::ResponsableTechnique->value => 'responsable-technique.dashboard',
            default => 'exposant.index',
        };
    }

    /** @return array{string, string} */
    private function welcomeFor(UserRole|string $role, ?string $name): array
    {
        $value = $role instanceof UserRole ? $role->value : $role;
        $message = match ($value) {
            UserRole::Admin->value => "Connexion réussie. Votre espace d'administration est prêt.",
            UserRole::Commercial->value => 'Connexion réussie. Retrouvez vos exposants et vos activités.',
            UserRole::ResponsableConferences->value => 'Connexion réussie. Retrouvez votre planning et vos réservations.',
            UserRole::ResponsableTechnique->value => 'Connexion réussie. Votre espace de suivi technique est prêt.',
            default => 'Connexion réussie. Heureux de vous retrouver dans votre espace exposant.',
        };

        $name = trim((string) $name);
        $parts = preg_split('/\s+/u', $name, 2) ?: [];
        $firstName = $parts[0] ?? '';
        if (
            $firstName === ''
            || str_contains($firstName, '@')
            || ! preg_match('/^[\p{L}\p{M}][\p{L}\p{M}\p{Pd}\x{2019}\x{27}]*$/u', $firstName)
        ) {
            $firstName = 'utilisateur';
        } else {
            $firstName = mb_strtoupper(mb_substr($firstName, 0, 1)).mb_substr($firstName, 1);
        }

        return [$message, $firstName];
    }
}
