<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Slot;
use App\Models\User;

/**
 * Le commercial gère l'ensemble du module (planning, statuts, demandes).
 * L'admin consulte le planning et les exports, sans rien modifier.
 * L'exposant n'accède qu'à ses propres créneaux.
 */
class SlotPolicy
{
    public function manage(User $user): bool
    {
        return $user->role === UserRole::Commercial && $user->is_active;
    }

    /** Consultation : planning, titulaires, CA et exports. */
    public function view(User $user): bool
    {
        return $user->is_active
            && in_array($user->role, [UserRole::Admin, UserRole::Commercial], true);
    }

    public function update(User $user, Slot $slot): bool
    {
        return $this->manage($user);
    }

    /**
     * Envoi du support, une fois le créneau confirmé :
     * par le titulaire exposant, ou par le commercial à sa place
     * (exposant qui n'y arrive pas, ou titulaire non exposant).
     */
    public function uploadSupport(User $user, Slot $slot): bool
    {
        return $slot->status->isSold()
            && ($this->manage($user) || $this->ownsSlot($user, $slot));
    }

    public function downloadSupport(User $user, Slot $slot): bool
    {
        return $slot->support_path !== null
            && ($this->view($user) || $this->ownsSlot($user, $slot));
    }

    private function ownsSlot(User $user, Slot $slot): bool
    {
        return $user->role === UserRole::Exposant
            && $slot->exhibitor_id !== null
            && $user->exhibitorProfile()->whereKey($slot->exhibitor_id)->exists();
    }
}
