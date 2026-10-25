@extends('layouts.app')

@section('title', 'Create account')

@section('content')
@include('admin.partials.shell-start')
    <header>
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Access management</p>
        <h1 class="mt-1 font-display text-3xl font-extrabold tracking-tight text-slate-950">Create staff account</h1>
        <p class="mt-2 text-slate-500">Set up administrator, counselor, or institute group-admin access.</p>
    </header>

    <section class="dashboard-panel max-w-4xl">
        <form method="POST" action="{{ route('admin.users.store') }}" class="grid gap-5 md:grid-cols-2">
            @csrf
            <div>
                <label for="staff-name" class="mb-1.5 block text-sm font-semibold">Full name</label>
                <input id="staff-name" name="name" value="{{ old('name') }}" required class="admin-form-control">
                @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="staff-email" class="mb-1.5 block text-sm font-semibold">Email address</label>
                <input id="staff-email" name="email" type="email" value="{{ old('email') }}" required class="admin-form-control">
                @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="staff-role" class="mb-1.5 block text-sm font-semibold">Account role</label>
                <select id="staff-role" name="role" required class="admin-form-control">
                    <option value="">Choose a role</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    <option value="counselor" @selected(old('role') === 'counselor')>Counselor</option>
                    <option value="group_admin" @selected(old('role') === 'group_admin')>Group admin</option>
                </select>
                @error('role') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="staff-password" class="mb-1.5 block text-sm font-semibold">Initial password</label>
                <input id="staff-password" name="password" type="password" autocomplete="new-password" required class="admin-form-control">
                <p class="mt-1 text-xs text-slate-500">At least 12 characters.</p>
                @error('password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="staff-password-confirmation" class="mb-1.5 block text-sm font-semibold">Confirm password</label>
                <input id="staff-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="admin-form-control">
            </div>
            <div>
                <label for="staff-institutes" class="mb-1.5 block text-sm font-semibold">Assign institutes to group admin</label>
                <select id="staff-institutes" name="institute_ids[]" multiple class="admin-form-control min-h-32">
                    @foreach ($institutes as $institute)
                        <option value="{{ $institute->id }}" @selected(in_array($institute->id, array_map('intval', (array) old('institute_ids', [])), true))>{{ $institute->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Use Ctrl/Cmd to select more than one institute.</p>
                @error('institute_ids') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                @error('institute_ids.*') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end md:col-span-2">
                <button class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white shadow-sm hover:bg-indigo-700">Create account</button>
            </div>
        </form>
    </section>
@include('admin.partials.shell-end')
@endsection
