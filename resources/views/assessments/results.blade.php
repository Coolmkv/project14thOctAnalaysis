@extends('layouts.app')

@section('title', 'Assessment results')

@section('content')
<div class="mx-auto max-w-6xl">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="mb-3 text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Assessment results</p>
            <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">{{ $assessment->title }}</h1>
        </div>
        <a href="{{ auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN) ? route('admin.dashboard') : route('assessments.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 font-bold text-slate-600 hover:bg-slate-50">Back to {{ auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN) ? 'admin dashboard' : 'library' }}</a>
    </div>

    @if ($studentSummaries->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <h2 class="font-display text-2xl font-bold text-slate-900">No submissions yet</h2>
            <p class="mt-2 text-slate-500">When students complete this assessment, their RIASEC profiles will appear here.</p>
        </div>
    @else
        <div class="mt-8 space-y-6">
            @foreach ($studentSummaries as $summary)
                @php
                    $profile = $summary['scores'];
                    $chartPoints = \App\Support\RiaSecScorer::chartPoints($profile);
                    $top = $summary['top'];
                @endphp
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Student profile</p>
                            <h2 class="mt-2 font-display text-2xl font-bold text-slate-900">{{ $summary['student']->name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">Submitted {{ $summary['submitted_at']->format('M j, Y') }}</p>
                        </div>
                        <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
                            <span class="font-bold">Top pattern:</span> {{ $top[0]['label'] }}
                        </div>
                    </div>

                    <div class="mt-6 grid gap-6 xl:grid-cols-[280px_1fr]">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
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
                                        $points = [];
                                        foreach (\App\Support\RiaSecScorer::ORDER as $index => $code) {
                                            $angle = -M_PI / 2 + ($index / count(\App\Support\RiaSecScorer::ORDER)) * (2 * M_PI);
                                            $x = 150 + cos($angle) * ($ring * 0.8);
                                            $y = 150 + sin($angle) * ($ring * 0.8);
                                            $points[] = $x.','.$y;
                                        }
                                    @endphp
                                    <polygon points="{{ implode(' ', $points) }}" fill="none" stroke="#e2e8f0" stroke-width="1" />
                                @endforeach

                                @php
                                    $polygon = [];
                                    foreach (\App\Support\RiaSecScorer::ORDER as $code) {
                                        [$x, $y] = $chartPoints[$code];
                                        $polygon[] = $x.','.$y;
                                    }
                                @endphp
                                <polygon points="{{ implode(' ', $polygon) }}" fill="rgba(79,70,229,0.2)" stroke="#4f46e5" stroke-width="2" />
                                @foreach (\App\Support\RiaSecScorer::ORDER as $code)
                                    @php
                                        [$x, $y] = $chartPoints[$code];
                                    @endphp
                                    <circle cx="{{ $x }}" cy="{{ $y }}" r="5" fill="#312e81" />
                                @endforeach
                            </svg>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                            @foreach (\App\Support\RiaSecScorer::ORDER as $code)
                                @php
                                    $details = \App\Support\RiaSecScorer::DOMAIN_DETAILS[$code];
                                @endphp
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-sm font-bold text-indigo-700">{{ $code }}</span>
                                        <span class="text-sm font-semibold text-slate-500">{{ $profile[$code] }}/{{ count($assessment->questions) }}</span>
                                    </div>
                                    <p class="mt-3 font-bold text-slate-900">{{ $details['label'] }}</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $details['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
