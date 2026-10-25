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

            @if (!empty($riaSecProfile))
                @php($topDomains = \App\Support\RiaSecScorer::topDomains($riaSecProfile))
                <div class="mt-7 rounded-2xl border border-indigo-200 bg-indigo-50/80 p-5">
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-700">RIASEC profile</p>
                    <h2 class="mt-3 font-display text-2xl font-bold text-slate-900">Your strongest interest patterns</h2>
                    @php($chartPoints = \App\Support\RiaSecScorer::chartPoints($riaSecProfile))
                    <div class="mt-5 grid gap-5 xl:grid-cols-[280px_1fr]">
                        <div class="rounded-2xl border border-indigo-100 bg-white p-4 shadow-sm">
                            <svg viewBox="0 0 300 300" class="mx-auto block h-64 w-full max-w-[280px]">
                                @foreach (\App\Support\RiaSecScorer::ORDER as $index => $code)
                                    @php
                                        $angle = -M_PI / 2 + ($index / count(\App\Support\RiaSecScorer::ORDER)) * (2 * M_PI);
                                        $axisX = 150 + cos($angle) * 110;
                                        $axisY = 150 + sin($angle) * 110;
                                        $labelX = 150 + cos($angle) * 128;
                                        $labelY = 150 + sin($angle) * 128;
                                    @endphp
                                    <line x1="150" y1="150" x2="{{ $axisX }}" y2="{{ $axisY }}" stroke="#dbe4ff" stroke-width="1" />
                                    <text x="{{ $labelX }}" y="{{ $labelY }}" text-anchor="middle" dominant-baseline="middle" font-size="13" font-family="sans-serif" fill="#4f46e5" font-weight="700">{{ $code }}</text>
                                @endforeach
                                @foreach ([25, 50, 75, 100] as $ring)
                                    @php
                                        $ringPoints = [];
                                        foreach (\App\Support\RiaSecScorer::ORDER as $index => $code) {
                                            $angle = -M_PI / 2 + ($index / count(\App\Support\RiaSecScorer::ORDER)) * (2 * M_PI);
                                            $ringPoints[] = (150 + cos($angle) * ($ring * 0.8)).','. (150 + sin($angle) * ($ring * 0.8));
                                        }
                                    @endphp
                                    <polygon points="{{ implode(' ', $ringPoints) }}" fill="none" stroke="#e2e8f0" stroke-width="1" />
                                @endforeach
                                @php
                                    $polygonPoints = [];
                                    foreach (\App\Support\RiaSecScorer::ORDER as $code) {
                                        [$x, $y] = $chartPoints[$code];
                                        $polygonPoints[] = $x.','.$y;
                                    }
                                @endphp
                                <polygon points="{{ implode(' ', $polygonPoints) }}" fill="rgba(79,70,229,0.2)" stroke="#4f46e5" stroke-width="2" />
                                @foreach (\App\Support\RiaSecScorer::ORDER as $code)
                                    @php
                                        [$x, $y] = $chartPoints[$code];
                                    @endphp
                                    <circle cx="{{ $x }}" cy="{{ $y }}" r="5" fill="#312e81" />
                                @endforeach
                            </svg>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($topDomains as $domain)
                                @php($details = \App\Support\RiaSecScorer::DOMAIN_DETAILS[$domain['code']])
                                <div class="rounded-xl border border-indigo-100 bg-white p-4 shadow-sm">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-sm font-bold text-indigo-700">{{ $domain['code'] }}</span>
                                        <span class="text-sm font-semibold text-slate-500">{{ $domain['score'] }}/{{ count($assessment->questions) }}</span>
                                    </div>
                                    <p class="mt-3 font-bold text-slate-900">{{ $details['label'] }}</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $details['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-5 rounded-xl border border-indigo-200 bg-white px-4 py-3 text-sm leading-6 text-slate-700">
                        <span class="font-bold text-slate-900">Top matches:</span>
                        {{ collect($topDomains)->take(3)->pluck('label')->join(', ') }}
                    </div>
                </div>
            @endif

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
