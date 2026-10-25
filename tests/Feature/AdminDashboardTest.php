<?php

namespace Tests\Feature;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_group_admin_have_separate_login_portals(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'password' => 'administrator-password',
        ]);
        $groupAdmin = User::factory()->create([
            'role' => User::ROLE_GROUP_ADMIN,
            'password' => 'group-admin-password',
        ]);

        $this->get(route('admin.login'))->assertOk()->assertSeeText('Admin portal');
        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'administrator-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'));
        $this->post(route('admin.login.store'), [
            'email' => $groupAdmin->email,
            'password' => 'group-admin-password',
        ])->assertSessionHasErrors('email');

        $this->post(route('group-admin.login.store'), [
            'email' => $groupAdmin->email,
            'password' => 'group-admin-password',
        ])->assertRedirect(route('group-admin.dashboard'));
    }

    public function test_admin_can_create_institutes_and_assign_multiple_to_group_admins(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        $this->post(route('admin.institutes.store'), ['name' => 'North School'])
            ->assertRedirect(route('admin.dashboard'));
        $this->post(route('admin.institutes.store'), ['name' => 'South Institute'])
            ->assertRedirect(route('admin.dashboard'));

        $institutes = Institute::query()->pluck('id')->all();
        $this->post(route('admin.users.store'), [
            'name' => 'Group Manager',
            'email' => 'manager@example.test',
            'role' => User::ROLE_GROUP_ADMIN,
            'password' => 'a-strong-password',
            'password_confirmation' => 'a-strong-password',
            'institute_ids' => $institutes,
        ])->assertRedirect(route('admin.dashboard'));

        $groupAdmin = User::where('email', 'manager@example.test')->firstOrFail();
        $this->assertSame(User::ROLE_GROUP_ADMIN, $groupAdmin->role);
        $this->assertCount(2, $groupAdmin->institutes);
    }

    public function test_admin_csv_import_creates_students_with_a_forced_password_change(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $institute = Institute::create(['name' => 'Central School']);

        $this->actingAs($admin)
            ->post(route('admin.students.import'), [
                'institute_id' => $institute->id,
                'students_file' => UploadedFile::fake()->createWithContent(
                    'students.csv',
                    "name,email\nRiley Student,riley@example.test\n",
                ),
            ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('temporary_password');

        $student = User::where('email', 'riley@example.test')->firstOrFail();
        $this->assertSame(User::ROLE_USER, $student->role);
        $this->assertTrue($student->must_change_password);
        $this->assertTrue(Hash::check(session('temporary_password'), $student->password));
        $this->assertTrue($student->institutes->contains($institute));

        $this->post(route('login.store'), [
            'email' => $student->email,
            'password' => session('temporary_password'),
        ])->assertRedirect(route('account.password.edit'));

        $this->get(route('student.assessments.index'))
            ->assertRedirect(route('account.password.edit'));

        $this->put(route('account.password.update'), [
            'password' => 'my-new-secure-password',
            'password_confirmation' => 'my-new-secure-password',
        ])->assertRedirect(route('student.assessments.index'));

        $this->assertFalse($student->fresh()->must_change_password);
        $this->assertTrue(Hash::check('my-new-secure-password', $student->fresh()->password));
    }

    public function test_group_admin_only_imports_students_to_assigned_institutes(): void
    {
        $assignedInstitute = Institute::create(['name' => 'Assigned School']);
        $otherInstitute = Institute::create(['name' => 'Other School']);
        $groupAdmin = User::factory()->create(['role' => User::ROLE_GROUP_ADMIN]);
        $groupAdmin->institutes()->attach($assignedInstitute);
        User::factory()->create([
            'name' => 'Other School Student',
            'role' => User::ROLE_USER,
        ])->institutes()->attach($otherInstitute);

        $this->actingAs($groupAdmin)
            ->get(route('group-admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Assigned School')
            ->assertDontSeeText('Other School Student');

        $file = UploadedFile::fake()->createWithContent(
            'students.csv',
            "name,email\nNew Student,new@example.test\n",
        );

        $this->post(route('group-admin.students.import', $otherInstitute), [
            'students_file' => $file,
        ])->assertForbidden();

        $this->post(route('group-admin.students.import', $assignedInstitute), [
            'students_file' => $file,
        ])->assertRedirect(route('group-admin.dashboard', ['institute' => $assignedInstitute->id]));

        $this->assertDatabaseHas('users', [
            'email' => 'new@example.test',
            'role' => User::ROLE_USER,
        ]);
    }
}
