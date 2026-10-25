@extends('layouts.admin')

@section('title', 'Import students')
@section('page-title', 'Import students')
@section('breadcrumb', 'Student onboarding')

@section('content')
@include('admin.partials.shell-start')
    <header>
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Student onboarding</p>
        <h1 class="mt-1 font-display text-3xl font-extrabold tracking-tight text-slate-950">Import students</h1>
        <p class="mt-2 text-slate-500">Create student accounts in bulk by assigning a CSV file to an institute.</p>
    </header>

    @include('admin.partials.temporary-password')

    <section class="dashboard-panel max-w-3xl">
        <h2 class="font-display text-xl font-bold text-slate-950">Upload student CSV</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">The file must include exactly two headers: <code class="rounded bg-slate-100 px-1.5 py-0.5">name,email</code>. Upload up to 500 students per batch. A shared temporary password will be generated; students must change it at first sign-in.</p>
        <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
            @csrf
            <div>
                <label for="import-institute" class="mb-1.5 block text-sm font-semibold">Institute</label>
                <select id="import-institute" name="institute_id" required class="admin-form-control">
                    <option value="">Choose an institute</option>
                    @foreach ($institutes as $institute)
                        <option value="{{ $institute->id }}" @selected(old('institute_id') == $institute->id)>{{ $institute->name }}</option>
                    @endforeach
                </select>
                @error('institute_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="students-file" class="mb-1.5 block text-sm font-semibold">CSV file</label>
                <input id="students-file" name="students_file" type="file" accept=".csv,text/csv" required class="admin-form-control file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-semibold file:text-indigo-700">
                @error('students_file') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <button class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white shadow-sm hover:bg-indigo-700">Import student accounts</button>
        </form>
        <div class="mt-7 rounded-xl bg-slate-50 p-4">
            <h3 class="text-sm font-bold text-slate-800">CSV example</h3>
            <pre class="mt-2 overflow-x-auto text-sm text-slate-600">name,email
Alex Morgan,alex@example.com
Jordan Lee,jordan@example.com</pre>
        </div>
    </section>
@include('admin.partials.shell-end')
@endsection
