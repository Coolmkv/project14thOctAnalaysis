@extends('layouts.app')

@section('title', 'Your assessments')

@section('content')
<div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
    <div><p class="mb-3 text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Counselor workspace</p><h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Assessment library</h1><p class="mt-3 max-w-xl text-slate-500">Create thoughtful question sets to help students reflect on their strengths, interests, and next steps.</p></div>
    <a href="{{ route('assessments.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700"><span class="text-xl">+</span> Create assessment</a>
</div>
<div class="mt-9 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @forelse ($assessments as $assessment)
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-3"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $assessment->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($assessment->status) }}</span><span class="text-sm text-slate-400">{{ $assessment->created_at->format('M j, Y') }}</span></div>
            <h2 class="mt-5 font-display text-xl font-bold text-slate-900">{{ $assessment->title }}</h2>
            <p class="mt-2 min-h-12 text-sm leading-6 text-slate-500">{{ $assessment->description ?: 'No description added.' }}</p>
            <div class="mt-5 border-t border-slate-100 pt-4 text-sm font-semibold text-slate-500">{{ $assessment->questions_count }} {{ \Illuminate\Support\Str::plural('question', $assessment->questions_count) }}</div>
            <div class="mt-5 flex items-center justify-between gap-3">
                <span class="text-sm text-slate-500">{{ $assessment->submissions_count }} completed</span>
                <a href="{{ route('assessments.results', $assessment) }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">View results</a>
            </div>
        </article>
    @empty
        <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-2xl text-indigo-600">✳</span>
            <h2 class="mt-5 font-display text-xl font-bold text-slate-900">Your first assessment starts here</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">Create a short, welcoming question set to help students begin exploring possible career directions.</p>
            <a href="{{ route('assessments.create') }}" class="mt-6 inline-flex rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white hover:bg-indigo-700">Build an assessment</a>
        </div>
    @endforelse
</div>
@endsection