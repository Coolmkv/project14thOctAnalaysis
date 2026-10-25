<?php

namespace Tests\Feature;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_admin_and_assigned_group_admin_accounts_idempotently(): void
    {
        $this->seed();
        $this->seed();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $groupAdmin = User::where('email', 'group-admin@example.com')->firstOrFail();

        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertSame(User::ROLE_GROUP_ADMIN, $groupAdmin->role);
        $this->assertSame(1, User::where('email', 'admin@example.com')->count());
        $this->assertSame(1, User::where('email', 'group-admin@example.com')->count());
        $this->assertTrue(
            $groupAdmin->institutes()->where('name', 'Demo Institute')->exists(),
        );
        $this->assertSame(1, Institute::where('name', 'Demo Institute')->count());
    }
}
