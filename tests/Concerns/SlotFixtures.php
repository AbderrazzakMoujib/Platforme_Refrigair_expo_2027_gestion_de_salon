<?php

namespace Tests\Concerns;

use App\Models\Exhibitor;
use App\Models\Slot;
use App\Models\User;
use Database\Seeders\ConferenceLoungeSeeder;

trait SlotFixtures
{
    private int $fixtureSequence = 0;

    protected function commercial(): User
    {
        return User::factory()->create(['role' => 'commercial', 'is_active' => true]);
    }

    /** Exposant rattaché à un commercial, avec (optionnellement) un compte exposant. */
    protected function exhibitor(string $company, bool $withAccount = true, ?User $commercial = null): Exhibitor
    {
        $this->fixtureSequence++;
        $commercial ??= $this->commercial();

        $exhibitor = $commercial->exhibitors()->create([
            'company_name' => $company,
            'contact_name' => 'Contact '.$company,
            'email' => 'contact'.$this->fixtureSequence.'@slots.test',
            'phone' => '+212600000000',
            'stand_number' => 'S'.$this->fixtureSequence,
            'area_sqm' => 12,
            'authorized_badges' => 2,
        ]);

        if ($withAccount) {
            $user = User::factory()->create(['role' => 'exposant', 'is_active' => true]);
            $exhibitor->user()->associate($user)->save();
        }

        return $exhibitor->fresh('user');
    }

    protected function seedSlots(): void
    {
        $this->seed(ConferenceLoungeSeeder::class);
    }

    protected function slot(string $code): Slot
    {
        return Slot::query()->where('code', $code)->firstOrFail();
    }
}
