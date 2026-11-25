<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\CommercialInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class CommercialInvitationController extends Controller
{
    private const INVITABLE_ROLES = [
        'commercial',
        'responsable_conferences',
        'exposant',
    ];

    public function show(string $token): View
    {
        $invitation = $this->findByToken($token);
        $state = $this->stateFor($invitation);

        return view('auth.accept-account-invitation', [
            'state' => $state,
            'token' => $state === 'valid' ? $token : null,
            'email' => $state === 'valid' ? $invitation?->user?->email : null,
            'name' => $state === 'valid' ? $invitation?->user?->name : null,
            'roleLabel' => $state === 'valid'
                ? $this->roleLabel($invitation->role)
                : null,
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $key = 'account-activation:'.hash('sha256', $token).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'password' => 'Trop de tentatives. Réessaie dans quelques minutes.',
            ]);
        }
        RateLimiter::hit($key, 60);

        $request->validate([
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $result = DB::transaction(function () use ($request, $token): string {
            $invitation = CommercialInvitation::query()
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if (! $invitation) {
                return 'invalid';
            }

            $user = User::query()->lockForUpdate()->find($invitation->user_id);
            if (
                ! $user
                || ! in_array($invitation->role, self::INVITABLE_ROLES, true)
                || $this->roleValue($user) !== $invitation->role
            ) {
                return 'invalid';
            }

            if ($invitation->accepted_at !== null || $user->password !== null) {
                return 'used';
            }

            if ($invitation->delivery_status === CommercialInvitation::STATUS_REVOKED || ! $user->is_active) {
                return 'revoked';
            }

            if ($invitation->expires_at->lessThanOrEqualTo(now())) {
                return 'expired';
            }

            if ($invitation->delivery_status !== CommercialInvitation::STATUS_SENT) {
                return 'unavailable';
            }

            $user->forceFill([
                'password' => $request->string('password')->toString(),
                'email_verified_at' => now(),
            ])->save();

            $invitation->forceFill(['accepted_at' => now()])->save();

            $user->invitations()
                ->whereNull('accepted_at')
                ->whereKeyNot($invitation->id)
                ->update(['delivery_status' => CommercialInvitation::STATUS_REVOKED]);

            return 'accepted';
        });

        if ($result !== 'accepted') {
            return redirect()
                ->route('account-activation.show', ['token' => $token])
                ->with('activation_state', $result);
        }

        RateLimiter::clear($key);

        return redirect()->route('login')->with(
            'status',
            'Votre compte est activé. Vous pouvez maintenant vous connecter.'
        );
    }

    private function findByToken(string $token): ?CommercialInvitation
    {
        return CommercialInvitation::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $token))
            ->first();
    }

    private function stateFor(?CommercialInvitation $invitation): string
    {
        if (! $invitation || ! $invitation->user) {
            return 'invalid';
        }

        $user = $invitation->user;
        if (
            ! in_array($invitation->role, self::INVITABLE_ROLES, true)
            || $this->roleValue($user) !== $invitation->role
        ) {
            return 'invalid';
        }

        if ($invitation->accepted_at !== null || $user->password !== null) {
            return 'used';
        }

        if ($invitation->delivery_status === CommercialInvitation::STATUS_REVOKED || ! $user->is_active) {
            return 'revoked';
        }

        if ($invitation->expires_at->lessThanOrEqualTo(now())) {
            return 'expired';
        }

        if ($invitation->delivery_status === CommercialInvitation::STATUS_FAILED) {
            return 'failed';
        }

        return $invitation->delivery_status === CommercialInvitation::STATUS_SENT
            ? 'valid'
            : 'unavailable';
    }

    private function roleValue(User $user): string
    {
        return $user->role instanceof UserRole ? $user->role->value : (string) $user->role;
    }

    private function roleLabel(string $role): string
    {
        return match ($role) {
            UserRole::Commercial->value => 'Commercial',
            UserRole::ResponsableConferences->value => 'Responsable Conférences',
            default => 'Exposant',
        };
    }
}
