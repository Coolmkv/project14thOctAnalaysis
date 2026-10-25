@extends('layouts.app')

@php
    $portalLabels = [
        'student' => ['Student sign in', 'Student portal', 'Sign in to explore and take career reflection assessments.'],
        'counselor' => ['Counselor sign in', 'Counselor workspace', 'Sign in to create and manage student career assessments.'],
        'admin' => ['Admin sign in', 'Admin portal', 'Manage staff accounts, institutes, students, and assessment activity.'],
        'group_admin' => ['Group admin sign in', 'Institute workspace', 'View students and assessment activity for your assigned institutes.'],
    ];
    [$pageTitle, $portalTitle, $portalDescription] = $portalLabels[$audience] ?? $portalLabels['student'];
    $loginRoute = match ($audience) {
        'admin' => route('admin.login.store'),
        'group_admin' => route('group-admin.login.store'),
        'counselor' => route('counselor.login.store'),
        default => route('login.store'),
    };
@endphp

@section('title', $pageTitle)

@section('content')
<div class="mx-auto grid max-w-5xl overflow-hidden rounded-3xl bg-white shadow-xl shadow-slate-200/60 lg:grid-cols-[1fr_0.9fr]">
    <section class="p-8 sm:p-12 lg:p-14">
        <p class="mb-3 text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">{{ $portalTitle }}</p>
        <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Welcome back.</h1>
        <p class="mt-3 text-slate-500">{{ $portalDescription }}</p>
        <form method="POST" action="{{ $loginRoute }}" class="mt-8 space-y-5">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                @error('email') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-xl border border-slate-200 px-4 py-3 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                @error('password') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"> Remember me</label>
            <button class="w-full rounded-xl bg-indigo-600 px-5 py-3.5 font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">Sign in</button>
        </form>
        @if ($audience === 'student')
            <p class="mt-6 text-center text-sm text-slate-500">New to {{ config('app.name', 'Mindscape Navigation') }}? <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Create a student account</a></p>
            <p class="mt-4 text-center text-sm text-slate-500">Are you a counselor? <a href="{{ route('counselor.login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Counselor sign in</a></p>
            <p class="mt-2 text-center text-sm text-slate-500">Administrator? <a href="{{ route('admin.login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Admin sign in</a></p>
            <p class="mt-2 text-center text-sm text-slate-500">Managing an institute? <a href="{{ route('group-admin.login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Group admin sign in</a></p>
        @else
            <p class="mt-6 text-center text-sm text-slate-500">Student? <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Go to student sign in</a></p>
            @if ($audience === 'counselor')
                <p class="mt-2 text-center text-sm text-slate-500">Administrator? <a href="{{ route('admin.login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Admin sign in</a></p>
            @endif
        @endif
    </section>
    <aside class="hidden flex-col justify-between bg-indigo-700 p-12 text-white lg:flex">
        <div><span class="text-5xl">✳</span><h2 class="mt-10 font-display text-3xl font-bold leading-tight">Make every next step feel possible.</h2><p class="mt-4 max-w-sm leading-7 text-indigo-100">{{ $portalDescription }}</p></div>
        <p class="text-sm text-indigo-200">A brighter path starts with a better question.</p>
    </aside>
</div>
@endsection