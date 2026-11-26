<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Mail\CommercialInvitationMail;
use App\Models\CommercialInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class AccountInvitationService
{
    private const INVITABLE_ROLES = [
        UserRole::Commercial->value,
        UserRole::ResponsableConferences->value,
        UserRole::ResponsableTechnique->value,
        UserRole::Exposant->value,
    ];

    /** @return array{invitation: CommercialInvitation, token: string} */
    public function issueFor(User $user): array
    {
        $role = $this->roleValue($user);

        abort_unless(in_array($role, self::INVITABLE_ROLES, true), 422);
        abort_unless($user->is_active && $user->password === null, 409);

        $token = Str::random(64);
        $invitation = $user->invitations()->create([
            'role' => $role,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours(48),
            'sent_at' => null,
            'accepted_at' => null,
            'delivery_status' => CommercialInvitation::STATUS_PENDING,
        ]);

        return compact('invitation', 'token');
    }

    /** Invalidate all unconsumed links, for example when access is suspended. */
    public function revokeOutstanding(User $user): void
    {
        CommercialInvitation::query()
            ->where('user_id', $user->id)
            ->whereNull('accepted_at')
            ->update(['delivery_status' => CommercialInvitation::STATUS_REVOKED]);
    }

    /**
     * Prepare a replacement invitation while serializing repeated clicks.
     * A recent successful/pending send is treated as the same request.
     *
     * @return array{invitation?: CommercialInvitation, token?: string, reason?: string}
     */
    public function prepareResend(User $user): array
    {
        return DB::transaction(function () use ($user): array {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if (! in_array($this->roleValue($lockedUser), self::INVITABLE_ROLES, true)) {
                return ['reason' => 'invalid_role'];
            }

            if ($lockedUser->password !== null) {
                return ['reason' => 'activated'];
            }

            if (! $lockedUser->is_active) {
                return ['reason' => 'disabled'];
            }

            $latest = $lockedUser->invitations()->latest('id')->first();
            if (
                $latest
                && $latest->created_at?->greaterThan(now()->subMinute())
                && in_array($latest->delivery_status, [
                    CommercialInvitation::STATUS_PENDING,
                    CommercialInvitation::STATUS_SENDING,
                    CommercialInvitation::STATUS_SENT,
                    CommercialInvitation::STATUS_FAILED,
                ], true)
            ) {
                return ['reason' => 'recent'];
            }

            $this->revokeOutstanding($lockedUser);

            return $this->issueFor($lockedUser);
        });
    }

    /** Send synchronously after the account/invitation transaction committed. */
    public function deliver(CommercialInvitation $invitation, string $token): bool
    {
        $claim = DB::transaction(function () use ($invitation): ?array {
            $lockedInvitation = CommercialInvitation::query()
                ->lockForUpdate()
                ->find($invitation->id);

            if (! $lockedInvitation || $lockedInvitation->delivery_status !== CommercialInvitation::STATUS_PENDING) {
                return null;
            }

            $user = User::query()->lockForUpdate()->find($lockedInvitation->user_id);
            if (
                ! $user
                || ! $user->is_active
                || $user->password !== null
                || $this->roleValue($user) !== $lockedInvitation->role
                || ! in_array($lockedInvitation->role, self::INVITABLE_ROLES, true)
            ) {
                $lockedInvitation->update([
                    'delivery_status' => CommercialInvitation::STATUS_REVOKED,
                ]);

                return null;
            }

            $lockedInvitation->update([
                'delivery_status' => CommercialInvitation::STATUS_SENDING,
            ]);

            return [
                'invitation' => $lockedInvitation,
                'user' => $user,
            ];
        });

        if (! $claim) {
            return false;
        }

        /** @var CommercialInvitation $lockedInvitation */
        $lockedInvitation = $claim['invitation'];
        /** @var User $user */
        $user = $claim['user'];

        try {
            $activationUrl = rtrim((string) config('app.url'), '/')
                .route('account-activation.show', ['token' => $token], false);

            Mail::to($user->email)->send(new CommercialInvitationMail(
                user: $user,
                role: $lockedInvitation->role,
                activationUrl: $activationUrl,
            ));
        } catch (Throwable) {
            CommercialInvitation::query()
                ->whereKey($lockedInvitation->id)
                ->where('delivery_status', CommercialInvitation::STATUS_SENDING)
                ->whereNull('accepted_at')
                ->update(['delivery_status' => CommercialInvitation::STATUS_FAILED]);

            // Do not log the exception: mail transport errors may contain the URL.
            return false;
        }

        return CommercialInvitation::query()
            ->whereKey($lockedInvitation->id)
            ->where('delivery_status', CommercialInvitation::STATUS_SENDING)
            ->whereNull('accepted_at')
            ->update([
                'delivery_status' => CommercialInvitation::STATUS_SENT,
                'sent_at' => now(),
            ]) === 1;
    }

    private function roleValue(User $user): string
    {
        return $user->role instanceof UserRole
            ? $user->role->value
            : (string) $user->role;
    }
}
