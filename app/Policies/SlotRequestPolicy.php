<?php

namespace App\Policies;

use App\Enums\SlotRequestStatus;
use App\Enums\UserRole;
use App\Models\SlotRequest;
use App\Models\User;

class SlotRequestPolicy
{
    public function create(User $user): bool
    {
        return $user->role === UserRole::Exposant
            && $user->exhibitorProfile()->exists();
    }

    /** Accepter / refuser : réservé au commercial, demande encore en attente. */
    public function process(User $user, SlotRequest $slotRequest): bool
    {
        return $user->role === UserRole::Commercial
            && $user->is_active
            && $slotRequest->status === SlotRequestStatus::Pending;
    }
}
