@extends('layouts.app')

@section('title', 'Institute dashboard')

@section('content')
<div class="space-y-8">
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Group administration</p>
        <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Institute dashboard</h1>
        <p class="mt-2 text-slate-600">View students and assessment activity for institutes assigned to your account.</p>
    </div>

    @if (session('temporary_password'))
        <section class="rounded-2xl border border-amber-300 bg-amber-50 p-5 text-amber-950" role="status">
            <h2 class="font-bold">Temporary student password — shown once</h2>
            <p class="mt-1 text-sm">Share this password securely with the students from this import. Each student must change it at first sign-in.</p>
            <code class="mt-3 inline-block rounded-lg bg-white px-4 py-2 font-mono text-lg font-bold">{{ session('temporary_password') }}</code>
        </section>
    @endif

    @if ($institutes->isEmpty())
        <section class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <h2 class="font-display text-xl font-bold text-slate-900">No institute assigned</h2>
            <p class="mt-2 text-slate-600">Ask an admin to assign one or more institutes to your account.</p>
        </section>
    @else
        <form method="GET" action="{{ route('group-admin.dashboard') }}" class="max-w-lg">
            <label for="institute" class="mb-2 block text-sm font-semibold">Choose institute</label>
            <div class="flex gap-3">
                <select id="institute" name="institute" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2.5">
                    @foreach ($institutes as $institute)
                        <option value="{{ $institute->id }}" @selected($selectedInstitute?->id === $institute->id)>{{ $institute->name }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-slate-900 px-4 py-2.5 font-bold text-white">View</button>
            </div>
        </form>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="font-display text-xl font-bold text-slate-900">Add students to {{ $selectedInstitute->name }}</h2>
            <p class="mt-1 text-sm leading-6 text-slate-500">Upload a CSV with exactly two headers, <code>name,email</code>. A shared temporary password is generated for this batch; students must change it when they first sign in.</p>
            <form method="POST" action="{{ route('group-admin.students.import', $selectedInstitute) }}" enctype="multipart/form-data" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                @csrf
                <div class="flex-1">
                    <label for="students-file" class="mb-1.5 block text-sm font-semibold">Student CSV file</label>
                    <input id="students-file" name="students_file" type="file" accept=".csv,text/csv" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                    @error('students_file') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <button class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white hover:bg-indigo-700">Import students</button>
            </form>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="font-display text-xl font-bold text-slate-900">Students at {{ $selectedInstitute->name }}</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500"><tr><th class="py-3 pr-4">Name</th><th class="py-3 pr-4">Email</th><th class="py-3 pr-4">Assessments completed</th><th class="py-3">Recent activity</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($students as $student)
                            <tr>
                                <td class="py-3 pr-4 font-semibold">{{ $student->name }}</td>
                                <td class="py-3 pr-4">{{ $student->email }}</td>
                                <td class="py-3 pr-4">{{ $student->assessment_submissions_count }}</td>
                                <td class="py-3">
                                    @forelse ($student->assessmentSubmissions->take(3) as $submission)
                                        <span class="block">{{ $submission->assessment->title }} <span class="text-slate-500">· {{ $submission->submitted_at->format('M j, Y') }}</span></span>
                                    @empty
                                        <span class="text-slate-500">No assessments completed</span>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-5 text-center text-slate-500">No students at this institute yet. Import a CSV to get started.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $students->links() }}</div>
        </section>
    @endif
</div>
@endsection
