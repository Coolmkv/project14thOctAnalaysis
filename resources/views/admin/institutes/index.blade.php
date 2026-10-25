@extends('layouts.app')

@section('title', 'Institutes')

@section('content')
@include('admin.partials.shell-start')
    <header>
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Organization</p>
        <h1 class="mt-1 font-display text-3xl font-extrabold tracking-tight text-slate-950">Institutes</h1>
        <p class="mt-2 text-slate-500">Create schools and manage membership assignments.</p>
    </header>

    <section class="dashboard-panel">
        <h2 class="font-display text-xl font-bold text-slate-950">Add an institute</h2>
        <form method="POST" action="{{ route('admin.institutes.store') }}" class="mt-4 flex flex-col gap-3 sm:flex-row">
            @csrf
            <div class="flex-1">
                <label for="institute-name" class="sr-only">Institute name</label>
                <input id="institute-name" name="institute_name" value="{{ old('institute_name') }}" placeholder="School or institute name" required class="admin-form-control">
                @error('institute_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <button class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white hover:bg-indigo-700">Create institute</button>
        </form>
    </section>

    <section class="dashboard-panel">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-xl font-bold text-slate-950">All institutes</h2>
            <span class="text-sm text-slate-500">{{ $institutes->count() }} total</span>
        </div>
        <div class="overflow-x-auto">
            <table class="admin-data-table min-w-full">
                <thead><tr><th>Institute</th><th>Members</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                    @forelse ($institutes as $institute)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $institute->name }}</td>
                            <td>{{ number_format($institute->users_count) }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('admin.institutes.destroy', $institute) }}" data-confirm="Delete {{ $institute->name }}? Student accounts and assessment results will be kept, but institute links and group-admin assignments will be removed.">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg px-3 py-2 font-semibold text-rose-700 hover:bg-rose-50">Delete institute</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-10 text-center text-slate-500">No institutes created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@include('admin.partials.shell-end')
@endsection
