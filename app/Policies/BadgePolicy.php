<?php
namespace App\Policies;
use App\Models\Badge;
use App\Models\User;
class BadgePolicy
{
    public function update(User $user, Badge $badge): bool { return $user->role->value === 'exposant' && $user->exhibitorProfile()->whereKey($badge->exhibitor_id)->exists(); }
    public function delete(User $user, Badge $badge): bool { return $this->update($user, $badge); }
}
