<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_default_to_the_user_role(): void
    {
        $user = User::factory()->create();

        $this->assertSame(User::ROLE_USER, $user->fresh()->role);
        $this->assertSame(
            [User::ROLE_ADMIN, User::ROLE_USER, User::ROLE_COUNSELOR, User::ROLE_GROUP_ADMIN],
            User::ROLES,
        );
    }

    public function test_public_registration_cannot_assign_a_privileged_role(): void
    {
        $this->post(route('register.store'), [
            'name' => 'New Account',
            'email' => 'new-account@example.test',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'role' => User::ROLE_ADMIN,
        ])->assertRedirect(route('student.assessments.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'new-account@example.test',
            'role' => User::ROLE_USER,
        ]);
    }

    public function test_role_middleware_restricts_a_route_to_the_configured_role(): void
    {
        Route::get('/role-protection-test', fn () => response('authorized'))
            ->middleware(['auth', 'role:admin']);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/role-protection-test')
            ->assertForbidden();

        $admin = User::factory()->create();
        $admin->role = User::ROLE_ADMIN;
        $admin->save();

        $this->actingAs($admin)
            ->get('/role-protection-test')
            ->assertOk()
            ->assertSeeText('authorized');
    }
}
