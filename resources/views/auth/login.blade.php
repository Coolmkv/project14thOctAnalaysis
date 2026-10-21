@extends('layouts.app')

@section('title', $audience === 'counselor' ? 'Counselor sign in' : 'Student sign in')

@section('content')
<div class="mx-auto grid max-w-5xl overflow-hidden rounded-3xl bg-white shadow-xl shadow-slate-200/60 lg:grid-cols-[1fr_0.9fr]">
    <section class="p-8 sm:p-12 lg:p-14">
        <p class="mb-3 text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">{{ $audience === 'counselor' ? 'Counselor workspace' : 'Student portal' }}</p>
        <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Welcome back.</h1>
        <p class="mt-3 text-slate-500">{{ $audience === 'counselor' ? 'Sign in to create and manage student career assessments.' : 'Sign in to explore and take career reflection assessments.' }}</p>
        <form method="POST" action="{{ $audience === 'counselor' ? route('counselor.login.store') : route('login.store') }}" class="mt-8 space-y-5">
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
            <p class="mt-6 text-center text-sm text-slate-500">New to Pathfinder? <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Create a student account</a></p>
            <p class="mt-4 text-center text-sm text-slate-500">Are you a counselor? <a href="{{ route('counselor.login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Counselor sign in</a></p>
        @else
            <p class="mt-6 text-center text-sm text-slate-500">Student? <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Go to student sign in</a></p>
        @endif
    </section>
    <aside class="hidden flex-col justify-between bg-indigo-700 p-12 text-white lg:flex">
        <div><span class="text-5xl">✳</span><h2 class="mt-10 font-display text-3xl font-bold leading-tight">Make every next step feel possible.</h2><p class="mt-4 max-w-sm leading-7 text-indigo-100">{{ $audience === 'counselor' ? 'Build thoughtful assessments that help students understand their interests and explore what comes next.' : 'Discover your interests, reflect on your strengths, and explore what might come next.' }}</p></div>
        <p class="text-sm text-indigo-200">A brighter path starts with a better question.</p>
    </aside>
</div>
@endsection