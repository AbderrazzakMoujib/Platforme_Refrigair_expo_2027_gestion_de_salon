<?php

namespace Tests\Feature;

use App\Models\Slot;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seeding_skips_demo_user_and_keeps_conference_slots(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        app(DatabaseSeeder::class)->setContainer($this->app)->run();

        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
        $this->assertSame(23, Slot::query()->count());
        $this->assertSame(0, User::query()->count());
    }
}
