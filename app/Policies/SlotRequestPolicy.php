<?php

namespace App\Policies;

use App\Enums\SlotRequestStatus;
use App\Enums\UserRole;
use App\Models\SlotRequest;
use App\Models\User;
use App\Models\Exhibitor;

class SlotRequestPolicy
{
    public function create(User $user): bool
    {
        return $user->role === UserRole::Exposant
            && $user->is_active
            && $user->exhibitorProfile()->exists();
    }

    public function createFor(User $user, Exhibitor $exhibitor): bool
    {
        return $user->role === UserRole::Commercial
            && $user->is_active
            && (int) $exhibitor->commercial_id === (int) $user->id;
    }

    public function viewAny(User $user): bool
    {
        return $user->is_active
            && in_array($user->role, [UserRole::Commercial, UserRole::ResponsableConferences], true);
    }

    /** Accepter / refuser : réservé au commercial, demande encore en attente. */
    public function process(User $user, SlotRequest $slotRequest): bool
    {
        return $user->role === UserRole::ResponsableConferences
            && $user->is_active
            && $slotRequest->status === SlotRequestStatus::Pending;
    }

    public function viewOwn(User $user, SlotRequest $slotRequest): bool
    {
        return $user->role === UserRole::Commercial
            && $user->is_active
            && $slotRequest->exhibitor()->where('commercial_id', $user->id)->exists();
    }
}
