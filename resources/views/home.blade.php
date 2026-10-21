@extends('layouts.app')

@section('title', 'Career exploration for students')

@section('content')
<section class="overflow-hidden rounded-[2rem] bg-indigo-700 text-white shadow-xl shadow-indigo-200/60">
    <div class="grid items-center gap-10 px-7 py-12 sm:px-12 lg:grid-cols-[1.1fr_0.9fr] lg:px-16 lg:py-16">
        <div>
            <p class="mb-4 text-sm font-bold uppercase tracking-[0.18em] text-indigo-200">A little clarity goes a long way</p>
            <h1 class="max-w-2xl font-display text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl">Your future is yours to explore.</h1>
            <p class="mt-5 max-w-xl text-lg leading-8 text-indigo-100">Discover what interests you, reflect on your strengths, and take thoughtful next steps—one question at a time.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('login') }}" class="rounded-xl bg-white px-6 py-3.5 text-center font-bold text-indigo-700 shadow-lg transition hover:bg-indigo-50">Student sign in</a>
                <a href="{{ route('register') }}" class="rounded-xl border border-indigo-300 px-6 py-3.5 text-center font-bold text-white transition hover:bg-indigo-600">Create student account</a>
            </div>
            <p class="mt-6 text-sm text-indigo-200">Counselor? <a href="{{ route('counselor.login') }}" class="font-bold text-white underline decoration-indigo-300 underline-offset-4 hover:decoration-white">Sign in to your workspace</a></p>
        </div>
        <div class="hidden rounded-3xl border border-white/15 bg-white/10 p-7 backdrop-blur-sm lg:block">
            <div class="flex items-center justify-between"><span class="text-sm font-bold text-indigo-100">A path that fits you</span><span class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-300 text-xl text-indigo-900">✳</span></div>
            <div class="mt-8 space-y-4">
                <div class="rounded-2xl bg-white p-5 text-slate-900"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 font-bold text-indigo-700">01</span><div><p class="font-bold">Notice what you enjoy</p><p class="mt-1 text-sm text-slate-500">Start with curiosity, not pressure.</p></div></div></div>
                <div class="rounded-2xl bg-white/90 p-5 text-slate-900"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 font-bold text-emerald-700">02</span><div><p class="font-bold">Explore possible directions</p><p class="mt-1 text-sm text-slate-500">There is more than one good path.</p></div></div></div>
                <div class="rounded-2xl bg-white/80 p-5 text-slate-900"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 font-bold text-amber-700">03</span><div><p class="font-bold">Choose a next step</p><p class="mt-1 text-sm text-slate-500">Small experiments can teach you a lot.</p></div></div></div>
            </div>
        </div>
    </div>
</section>
<section class="mx-auto mt-10 max-w-3xl text-center">
    <p class="text-sm leading-6 text-slate-500">Assessments are reflection tools—not diagnoses or promises about a specific career. Your answers help you start a conversation about what could fit you.</p>
</section>
@endsection
