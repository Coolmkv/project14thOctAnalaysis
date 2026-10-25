@extends('layouts.app')

@section('title', 'Set a new password')

@section('content')
<section class="mx-auto max-w-xl rounded-3xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
    <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">One more step</p>
    <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-950">Set your password</h1>
    <p class="mt-3 leading-7 text-slate-600">You signed in with a temporary password. Create a personal password to continue.</p>
    <form method="POST" action="{{ route('account.password.update') }}" class="mt-7 space-y-5">
        @csrf
        @method('PUT')
        <div>
            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">New password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
            @error('password') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password-confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirm new password</label>
            <input id="password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
        </div>
        <button class="w-full rounded-xl bg-indigo-600 px-5 py-3.5 font-bold text-white hover:bg-indigo-700">Save password</button>
    </form>
</section>
@endsection
