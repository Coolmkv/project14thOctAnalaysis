@extends('layouts.app')

@section('title', 'Explore assessments')

@section('content')
<div class="max-w-3xl">
    <p class="mb-3 text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Your {{ config('app.name', 'Mindscape Navigation') }} space</p>
    <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Explore your interests</h1>
    <p class="mt-3 text-slate-500">Choose an assessment to reflect on your strengths, interests, and the kinds of activities you enjoy.</p>
</div>
<div class="mt-9 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
    @forelse ($assessments as $assessment)
        @php($completed = $assessment->submissions->isNotEmpty())
        <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $completed ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700' }}">{{ $completed ? 'Completed' : 'Ready to explore' }}</span>
                <span class="text-xs font-semibold text-slate-400">{{ $assessment->questions_count }} {{ \Illuminate\Support\Str::plural('question', $assessment->questions_count) }}</span>
            </div>
            <h2 class="mt-5 font-display text-xl font-bold text-slate-900">{{ $assessment->title }}</h2>
            <p class="mt-2 flex-1 text-sm leading-6 text-slate-500">{{ $assessment->description ?: 'Take a few minutes to reflect on what feels interesting to you.' }}</p>
            <a href="{{ route('student.assessments.show', $assessment) }}" class="mt-6 inline-flex items-center justify-center rounded-xl {{ $completed ? 'border border-slate-200 text-slate-700 hover:bg-slate-50' : 'bg-indigo-600 text-white hover:bg-indigo-700' }} px-4 py-3 text-sm font-bold transition">{{ $completed ? 'Review your answers' : 'Start assessment' }}</a>
        </article>
    @empty
        <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-2xl text-indigo-600">✳</span>
            <h2 class="mt-5 font-display text-xl font-bold text-slate-900">No assessments are published yet</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">Check back later—your counselor may be preparing an assessment for you.</p>
        </div>
    @endforelse
</div>
@endsection
