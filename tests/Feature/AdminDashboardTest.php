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

        $this->post(route('admin.institutes.store'), ['institute_name' => 'North School'])
            ->assertRedirect(route('admin.institutes.index'));
        $this->post(route('admin.institutes.store'), ['institute_name' => 'South Institute'])
            ->assertRedirect(route('admin.institutes.index'));

        $institutes = Institute::query()->pluck('id')->all();
        $this->post(route('admin.users.store'), [
            'name' => 'Group Manager',
            'email' => 'manager@example.test',
            'role' => User::ROLE_GROUP_ADMIN,
            'password' => 'a-strong-password',
            'password_confirmation' => 'a-strong-password',
            'institute_ids' => $institutes,
        ])->assertRedirect(route('admin.accounts.create'));

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
            ->assertRedirect(route('admin.students.import-form'))
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

    public function test_admin_staff_and_student_data_tables_return_paginated_rows(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $staff = User::factory()->create([
            'name' => 'Counselor Table',
            'role' => User::ROLE_COUNSELOR,
        ]);
        $student = User::factory()->create(['name' => 'Student Table']);

        $this->actingAs($admin)
            ->getJson(route('admin.data.staff', ['draw' => 3, 'start' => 0, 'length' => 10]))
            ->assertOk()
            ->assertJsonPath('draw', 3)
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonFragment(['name' => $staff->name]);

        $this->getJson(route('admin.data.students', [
            'draw' => 4,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => 'Student Table'],
        ]))
            ->assertOk()
            ->assertJsonPath('draw', 4)
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.name', $student->name);
    }

    public function test_admin_management_tools_are_available_on_separate_pages(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.accounts.create'))
            ->assertOk()
            ->assertSeeText('Create staff account');

        $this->get(route('admin.institutes.index'))
            ->assertOk()
            ->assertSeeText('All institutes');

        $this->get(route('admin.students.import-form'))
            ->assertOk()
            ->assertSeeText('Upload student CSV');

        $this->get(route('admin.staff.index'))
            ->assertOk()
            ->assertSee('data-datatable', false)
            ->assertSee('data-source', false);

        $this->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('data-datatable', false)
            ->assertSee('data-source', false);
    }

    public function test_deleting_an_institute_keeps_student_accounts_and_assessment_submissions(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $institute = Institute::create(['name' => 'Closing School']);
        $student = User::factory()->create();
        $student->institutes()->attach($institute);
        $assessment = User::factory()->create(['role' => User::ROLE_COUNSELOR])
            ->assessments()->create(['title' => 'Career reflection', 'status' => 'published']);
        $submission = $assessment->submissions()->create([
            'user_id' => $student->id,
            'answers' => ['question' => 'choice'],
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.institutes.destroy', $institute))
            ->assertRedirect(route('admin.institutes.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('institutes', ['id' => $institute->id]);
        $this->assertDatabaseHas('users', ['id' => $student->id]);
        $this->assertDatabaseHas('assessment_submissions', ['id' => $submission->id]);
        $this->assertDatabaseMissing('institute_user', [
            'institute_id' => $institute->id,
            'user_id' => $student->id,
        ]);
    }
}
