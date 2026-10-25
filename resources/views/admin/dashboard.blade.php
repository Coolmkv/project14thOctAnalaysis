@extends('layouts.app')

@section('title', 'Admin dashboard')

@section('content')
<div class="flex flex-col gap-6 lg:flex-row lg:items-start">
    <aside class="w-full shrink-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:sticky lg:top-6 lg:w-64">
        <p class="px-3 pb-3 text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Administration</p>
        <nav aria-label="Admin dashboard sections" class="flex gap-2 overflow-x-auto lg:flex-col">
            <a href="#overview" class="whitespace-nowrap rounded-xl bg-indigo-50 px-3 py-2.5 text-sm font-bold text-indigo-700">Overview</a>
            <a href="#accounts" class="whitespace-nowrap rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-indigo-700">Create account</a>
            <a href="#institutes" class="whitespace-nowrap rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-indigo-700">Institutes</a>
            <a href="#import-students" class="whitespace-nowrap rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-indigo-700">Import students</a>
            <a href="#staff" class="whitespace-nowrap rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-indigo-700">Staff</a>
            <a href="#students" class="whitespace-nowrap rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-indigo-700">Students</a>
            <a href="#assessment-activity" class="whitespace-nowrap rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-indigo-700">Assessments</a>
        </nav>
    </aside>

    <div class="min-w-0 flex-1 space-y-8">
        <section id="overview" class="scroll-mt-6">
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Administration</p>
            <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Admin dashboard</h1>
            <p class="mt-2 text-slate-600">Manage accounts, schools and student assessment data from one place.</p>
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">Staff accounts</p><p class="mt-2 font-display text-3xl font-extrabold text-slate-950">{{ $staffCount }}</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">Students</p><p class="mt-2 font-display text-3xl font-extrabold text-slate-950">{{ $studentCount }}</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">Institutes</p><p class="mt-2 font-display text-3xl font-extrabold text-slate-950">{{ $institutes->count() }}</p></div>
            </div>
        </section>

        @if (session('temporary_password'))
            <section class="rounded-2xl border border-amber-300 bg-amber-50 p-5 text-amber-950" role="status">
                <h2 class="font-bold">Temporary student password — shown once</h2>
                <p class="mt-1 text-sm">Share this password securely with the students from this import. Each student must change it at first sign-in.</p>
                <code class="mt-3 inline-block rounded-lg bg-white px-4 py-2 font-mono text-lg font-bold">{{ session('temporary_password') }}</code>
            </section>
        @endif

        <section id="accounts" class="scroll-mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.14em] text-indigo-600">Staff access</p>
                <h2 class="mt-1 font-display text-2xl font-bold text-slate-950">Create an account</h2>
                <p class="mt-1 text-sm text-slate-500">Create admin, counselor, and group-admin logins. Passwords must be at least 12 characters.</p>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
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
                <div>
                    <label for="staff-password-confirmation" class="mb-1.5 block text-sm font-semibold">Confirm password</label>
                    <input id="staff-password-confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                </div>
                <div>
                    <label for="staff-institutes" class="mb-1.5 block text-sm font-semibold">Group-admin institutes</label>
                    <select id="staff-institutes" name="institute_ids[]" multiple class="min-h-24 w-full rounded-lg border border-slate-300 px-3 py-2.5">
                        @foreach ($institutes as $institute)
                            <option value="{{ $institute->id }}" @selected(in_array($institute->id, array_map('intval', (array) old('institute_ids', [])), true))>{{ $institute->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Select one or more institutes for a group admin.</p>
                    @error('institute_ids') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    @error('institute_ids.*') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <button class="rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white hover:bg-indigo-700 md:col-span-2">Create staff account</button>
            </form>
        </section>

        <section id="institutes" class="scroll-mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.14em] text-indigo-600">Organization</p>
                <h2 class="mt-1 font-display text-2xl font-bold text-slate-950">Institutes</h2>
                <p class="mt-1 text-sm text-slate-500">Create schools or institutes, or remove ones that are no longer needed.</p>
            </div>
            <form method="POST" action="{{ route('admin.institutes.store') }}" class="mt-5 flex flex-col gap-3 sm:flex-row">
                @csrf
                <div class="flex-1">
                    <label for="institute-name" class="sr-only">Institute name</label>
                    <input id="institute-name" name="institute_name" value="{{ old('institute_name') }}" placeholder="School or institute name" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                    @error('institute_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <button class="rounded-xl bg-slate-900 px-5 py-3 font-bold text-white hover:bg-slate-700">Create institute</button>
            </form>
            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[520px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500"><tr><th class="py-3 pr-4">Institute</th><th class="py-3 pr-4">Members</th><th class="py-3 text-right">Action</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($institutes as $institute)
                            <tr>
                                <td class="py-3 pr-4 font-semibold">{{ $institute->name }}</td>
                                <td class="py-3 pr-4">{{ $institute->users_count }}</td>
                                <td class="py-3 text-right">
                                    <form method="POST" action="{{ route('admin.institutes.destroy', $institute) }}" data-confirm="Delete {{ $institute->name }}? Student accounts and assessment results will be kept, but institute links and group-admin assignments will be removed.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg px-3 py-2 font-semibold text-rose-700 hover:bg-rose-50">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-5 text-center text-slate-500">No institutes have been created yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section id="import-students" class="scroll-mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.14em] text-indigo-600">Student onboarding</p>
                <h2 class="mt-1 font-display text-2xl font-bold text-slate-950">Import students</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">Upload a CSV with <code>name,email</code> headers, up to 500 students per file. Each batch receives a shared temporary password that students must change at first sign-in.</p>
            </div>
            <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
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
                <button class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white hover:bg-indigo-700 md:col-span-2">Import student accounts</button>
            </form>
        </section>

        <section id="staff" class="scroll-mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.14em] text-indigo-600">Directory</p>
                    <h2 class="mt-1 font-display text-2xl font-bold text-slate-950">Staff</h2>
                </div>
                <span class="text-sm text-slate-500">{{ $staffCount }} accounts</span>
            </div>
            <div class="mt-5 overflow-x-auto">
                <table id="staff-table" data-datatable data-source="{{ route('admin.data.staff') }}" class="w-full min-w-[640px] text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th data-column="name" class="py-3 pr-4">Name</th>
                            <th data-column="email" class="py-3 pr-4">Email</th>
                            <th data-column="role" class="py-3 pr-4">Role</th>
                            <th data-column="institutes" data-sortable="false" class="py-3">Institutes</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </section>

        <section id="students" class="scroll-mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.14em] text-indigo-600">Directory</p>
                    <h2 class="mt-1 font-display text-2xl font-bold text-slate-950">Students</h2>
                </div>
                <span class="text-sm text-slate-500">{{ $studentCount }} accounts</span>
            </div>
            <div class="mt-5 overflow-x-auto">
                <table id="students-table" data-datatable data-source="{{ route('admin.data.students') }}" class="w-full min-w-[640px] text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th data-column="name" class="py-3 pr-4">Name</th>
                            <th data-column="email" class="py-3 pr-4">Email</th>
                            <th data-column="institutes" data-sortable="false" class="py-3 pr-4">Institute(s)</th>
                            <th data-column="completed_assessments" class="py-3">Completed assessments</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </section>

        <section id="assessment-activity" class="scroll-mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-bold uppercase tracking-[0.14em] text-indigo-600">Platform activity</p>
            <h2 class="mt-1 font-display text-2xl font-bold text-slate-950">Assessments</h2>
            <div class="mt-5 overflow-x-auto">
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
</div>
@endsection
