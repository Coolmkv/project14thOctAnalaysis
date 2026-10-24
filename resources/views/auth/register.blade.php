@extends('layouts.app')

@section('title', 'Create account')

@section('content')
<div class="mx-auto max-w-xl rounded-3xl bg-white p-8 shadow-xl shadow-slate-200/60 sm:p-12">
    <p class="mb-3 text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Get started</p>
    <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-950">Create your student account</h1>
    <p class="mt-3 text-slate-500">Join {{ config('app.name', 'Mindscape Navigation') }} to explore career reflection assessments at your own pace.</p>
    <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-5">
        @csrf
        <div><label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Full name</label><input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">@error('name') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror</div>
        <div><label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">@error('email') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror</div>
        <div><label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label><input id="password" name="password" type="password" autocomplete="new-password" required class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">@error('password') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror</div>
        <div><label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"></div>
        <button class="w-full rounded-xl bg-indigo-600 px-5 py-3.5 font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">Create account</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500">Already have an account? <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Sign in</a></p>
</div>
@endsection