@extends('layouts.app')

@section('title', 'Admin dashboard')

@section('content')
<div class="space-y-8">
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Administration</p>
        <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Admin dashboard</h1>
        <p class="mt-2 text-slate-600">Manage staff access, institutes, student accounts, and assessment activity.</p>
    </div>

    @if (session('temporary_password'))
        <section class="rounded-2xl border border-amber-300 bg-amber-50 p-5 text-amber-950" role="status">
            <h2 class="font-bold">Temporary student password — shown once</h2>
            <p class="mt-1 text-sm">Share this password securely with the students from this import. Each student must change it at first sign-in.</p>
            <code class="mt-3 inline-block rounded-lg bg-white px-4 py-2 font-mono text-lg font-bold">{{ session('temporary_password') }}</code>
        </section>
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="font-display text-xl font-bold text-slate-900">Create a staff account</h2>
            <p class="mt-1 text-sm text-slate-500">Admins can create admin, counselor, and group admin logins. Passwords must be at least 12 characters.</p>
            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label for="staff-name" class="mb-1.5 block text-sm font-semibold">Full name</label>
                    <input id="staff-name" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                    @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="staff-email" class="mb-1.5 block text-sm font-semibold">Email</label>
                    <input id="staff-email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                    @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="staff-role" class="mb-1.5 block text-sm font-semibold">Role</label>
                    <select id="staff-role" name="role" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                        <option value="">Choose a role</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                        <option value="counselor" @selected(old('role') === 'counselor')>Counselor</option>
                        <option value="group_admin" @selected(old('role') === 'group_admin')>Group admin</option>
                    </select>
                    @error('role') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="staff-password" class="mb-1.5 block text-sm font-semibold">Initial password</label>
                    <input id="staff-password" name="password" type="password" required autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                    @error('password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="staff-password-confirmation" class="mb-1.5 block text-sm font-semibold">Confirm password</label>
                    <input id="staff-password-confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                </div>
                <div class="sm:col-span-2">
                    <label for="staff-institutes" class="mb-1.5 block text-sm font-semibold">Assign institutes to group admin</label>
                    <select id="staff-institutes" name="institute_ids[]" multiple class="min-h-24 w-full rounded-lg border border-slate-300 px-3 py-2.5">
                        @foreach ($institutes as $institute)
                            <option value="{{ $institute->id }}" @selected(in_array($institute->id, array_map('intval', (array) old('institute_ids', [])), true))>{{ $institute->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Select one or more institutes when creating a group admin. Create institutes below first.</p>
                    @error('institute_ids') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    @error('institute_ids.*') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <button class="rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white hover:bg-indigo-700 sm:col-span-2">Create staff account</button>
            </form>
        </section>

        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-display text-xl font-bold text-slate-900">Add an institute</h2>
                <form method="POST" action="{{ route('admin.institutes.store') }}" class="mt-4 flex flex-col gap-3 sm:flex-row">
                    @csrf
                    <div class="flex-1">
                        <label for="institute-name" class="sr-only">Institute name</label>
                        <input id="institute-name" name="institute_name" value="{{ old('institute_name') }}" placeholder="School or institute name" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                        @error('institute_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <button class="rounded-xl bg-slate-900 px-5 py-3 font-bold text-white hover:bg-slate-700">Add institute</button>
                </form>
                <ul class="mt-4 divide-y divide-slate-100">
                    @forelse ($institutes as $institute)
                        <li class="flex justify-between py-2 text-sm"><span class="font-medium">{{ $institute->name }}</span><span class="text-slate-500">{{ $institute->users_count }} members</span></li>
                    @empty
                        <li class="py-3 text-sm text-slate-500">No institutes added yet.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-display text-xl font-bold text-slate-900">Import students from CSV</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">Create student accounts in an institute. CSV headers must be <code>name,email</code>; maximum 500 rows per upload.</p>
                <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="import-institute" class="mb-1.5 block text-sm font-semibold">Institute</label>
                        <select id="import-institute" name="institute_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                            <option value="">Choose an institute</option>
                            @foreach ($institutes as $institute)
                                <option value="{{ $institute->id }}" @selected(old('institute_id') == $institute->id)>{{ $institute->name }}</option>
                            @endforeach
                        </select>
                        @error('institute_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="students-file" class="mb-1.5 block text-sm font-semibold">Student CSV file</label>
                        <input id="students-file" name="students_file" type="file" accept=".csv,text/csv" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                        @error('students_file') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <button class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white hover:bg-indigo-700">Import students</button>
                </form>
            </section>
        </div>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-display text-xl font-bold text-slate-900">Staff accounts</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500"><tr><th class="py-3 pr-4">Name</th><th class="py-3 pr-4">Email</th><th class="py-3 pr-4">Role</th><th class="py-3">Institutes</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($staff as $member)
                        <tr><td class="py-3 pr-4 font-semibold">{{ $member->name }}</td><td class="py-3 pr-4">{{ $member->email }}</td><td class="py-3 pr-4">{{ str($member->role)->replace('_', ' ')->title() }}</td><td class="py-3">{{ $member->institutes->pluck('name')->join(', ') ?: '—' }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="py-5 text-center text-slate-500">No staff accounts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-display text-xl font-bold text-slate-900">Students across institutes</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500"><tr><th class="py-3 pr-4">Name</th><th class="py-3 pr-4">Email</th><th class="py-3 pr-4">Institute(s)</th><th class="py-3">Completed assessments</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($students as $student)
                        <tr><td class="py-3 pr-4 font-semibold">{{ $student->name }}</td><td class="py-3 pr-4">{{ $student->email }}</td><td class="py-3 pr-4">{{ $student->institutes->pluck('name')->join(', ') ?: '—' }}</td><td class="py-3">{{ $student->assessment_submissions_count }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="py-5 text-center text-slate-500">No students have been added yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $students->links() }}</div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-display text-xl font-bold text-slate-900">Assessment activity</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500"><tr><th class="py-3 pr-4">Assessment</th><th class="py-3 pr-4">Created by</th><th class="py-3 pr-4">Status</th><th class="py-3 pr-4">Submissions</th><th class="py-3">Results</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($assessments as $assessment)
                        <tr><td class="py-3 pr-4 font-semibold">{{ $assessment->title }}</td><td class="py-3 pr-4">{{ $assessment->author->name }}</td><td class="py-3 pr-4">{{ str($assessment->status)->title() }}</td><td class="py-3 pr-4">{{ $assessment->submissions_count }}</td><td class="py-3"><a class="font-bold text-indigo-600 hover:text-indigo-800" href="{{ route('assessments.results', $assessment) }}">View results</a></td></tr>
                    @empty
                        <tr><td colspan="5" class="py-5 text-center text-slate-500">No assessments have been created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
