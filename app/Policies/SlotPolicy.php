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
            && in_array($user->role, [UserRole::Admin, UserRole::Commercial, UserRole::ResponsableConferences], true);
    }

    public function update(User $user, Slot $slot): bool
    {
        if (! $this->manage($user)) {
            return false;
        }

        if (! $slot->status->isTaken()) {
            return true;
        }

        return $this->slotCommercialId($slot) === $user->id;
    }

    /**
     * Envoi du support, une fois le créneau confirmé :
     * par le titulaire exposant, ou par le commercial à sa place
     * (exposant qui n'y arrive pas, ou titulaire non exposant).
     */
    public function uploadSupport(User $user, Slot $slot): bool
    {
        return $slot->status->isSold()
            && ($this->commercialCanAccess($user, $slot) || $this->ownsSlot($user, $slot));
    }

    public function downloadSupport(User $user, Slot $slot): bool
    {
        if ($slot->support_path === null) {
            return false;
        }

        return in_array($user->role, [UserRole::Admin, UserRole::ResponsableConferences], true)
            ? $this->view($user)
            : ($this->commercialCanAccess($user, $slot) || $this->ownsSlot($user, $slot));
    }

    private function commercialCanAccess(User $user, Slot $slot): bool
    {
        return $this->manage($user)
            && $this->slotCommercialId($slot) === $user->id;
    }

    private function slotCommercialId(Slot $slot): ?int
    {
        if ($slot->exhibitor_id !== null) {
            return $slot->exhibitor?->commercial_id;
        }

        return $slot->commercial_id === null ? null : (int) $slot->commercial_id;
    }

    private function ownsSlot(User $user, Slot $slot): bool
    {
        return $user->role === UserRole::Exposant
            && $slot->exhibitor_id !== null
            && $user->exhibitorProfile()->whereKey($slot->exhibitor_id)->exists();
    }
}
