@extends('layouts.app')

@section('title', $assessment->title)

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('student.assessments.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">← All assessments</a>
    <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-6 sm:p-9">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Career reflection</p>
        <h1 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-950">{{ $assessment->title }}</h1>
        @if ($assessment->description)
            <p class="mt-3 leading-7 text-slate-600">{{ $assessment->description }}</p>
        @endif
        <div class="mt-5 rounded-xl bg-indigo-50 px-4 py-3 text-sm leading-6 text-indigo-900">There are no right or wrong answers. Choose the options that feel most like you. This activity is for reflection, not a diagnosis or a prediction of your future.</div>

        @if ($submission)
            <div class="mt-7 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800" role="status">Completed {{ $submission->submitted_at->format('M j, Y') }}. Your answers are saved below.</div>
            <div class="mt-7 space-y-7">
                @foreach ($assessment->questions as $question)
                    <section>
                        <h2 class="font-bold leading-6 text-slate-900">{{ $loop->iteration }}. {{ $question->prompt }}</h2>
                        <p class="mt-2 rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-700">{{ $submission->answers[$question->id] ?? 'No answer saved' }}</p>
                    </section>
                @endforeach
            </div>
        @else
            @if ($errors->any())
                <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('student.assessments.submit', $assessment) }}" class="mt-8">
                @csrf
                <div class="space-y-8">
                    @foreach ($assessment->questions as $question)
                        <fieldset>
                            <legend class="font-bold leading-6 text-slate-900">{{ $loop->iteration }}. {{ $question->prompt }} <span class="text-rose-500">*</span></legend>
                            <div class="mt-3 space-y-2">
                                @foreach ($question->choices as $choice)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 px-4 py-3 transition hover:border-indigo-300 hover:bg-indigo-50/50 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $choice }}" required @checked(old('answers.'.$question->id) === $choice) class="mt-1 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm leading-6 text-slate-700">{{ $choice }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>
                <div class="mt-9 flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('student.assessments.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center font-bold text-slate-600 hover:bg-slate-50">Back to assessments</a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">Submit my answers</button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
