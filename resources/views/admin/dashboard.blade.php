@extends('layouts.admin')

@section('title', 'Admin overview')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Overview')

@section('content')
@include('admin.partials.shell-start')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Overview</p>
            <h1 class="mt-1 font-display text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Good to see you, {{ auth()->user()->name }}</h1>
            <p class="mt-2 text-slate-500">Here’s what’s happening across your career exploration platform.</p>
        </div>
        <a href="{{ route('admin.accounts.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">
            <span class="text-lg leading-none" aria-hidden="true">+</span> Create account
        </a>
    </div>

    @include('admin.partials.temporary-password')

    <section aria-label="Platform summary" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="dashboard-stat-card border-indigo-100 bg-gradient-to-br from-indigo-50 to-white">
            <div class="flex items-center justify-between"><span class="text-sm font-semibold text-slate-500">Students</span><span class="dashboard-stat-icon bg-indigo-100 text-indigo-700">S</span></div>
            <p class="mt-5 font-display text-3xl font-extrabold text-slate-950">{{ number_format($studentCount) }}</p>
            <a href="{{ route('admin.students.index') }}" class="mt-2 inline-flex items-center gap-1 text-sm font-bold text-indigo-700 hover:text-indigo-900">View students <span aria-hidden="true">→</span></a>
        </article>
        <article class="dashboard-stat-card border-sky-100 bg-gradient-to-br from-sky-50 to-white">
            <div class="flex items-center justify-between"><span class="text-sm font-semibold text-slate-500">Staff accounts</span><span class="dashboard-stat-icon bg-sky-100 text-sky-700">T</span></div>
            <p class="mt-5 font-display text-3xl font-extrabold text-slate-950">{{ number_format($staffCount) }}</p>
            <a href="{{ route('admin.staff.index') }}" class="mt-2 inline-flex items-center gap-1 text-sm font-bold text-sky-700 hover:text-sky-900">Manage staff <span aria-hidden="true">→</span></a>
        </article>
        <article class="dashboard-stat-card border-emerald-100 bg-gradient-to-br from-emerald-50 to-white">
            <div class="flex items-center justify-between"><span class="text-sm font-semibold text-slate-500">Institutes</span><span class="dashboard-stat-icon bg-emerald-100 text-emerald-700">I</span></div>
            <p class="mt-5 font-display text-3xl font-extrabold text-slate-950">{{ number_format($instituteCount) }}</p>
            <a href="{{ route('admin.institutes.index') }}" class="mt-2 inline-flex items-center gap-1 text-sm font-bold text-emerald-700 hover:text-emerald-900">Manage institutes <span aria-hidden="true">→</span></a>
        </article>
        <article class="dashboard-stat-card border-amber-100 bg-gradient-to-br from-amber-50 to-white">
            <div class="flex items-center justify-between"><span class="text-sm font-semibold text-slate-500">Completed assessments</span><span class="dashboard-stat-icon bg-amber-100 text-amber-700">✓</span></div>
            <p class="mt-5 font-display text-3xl font-extrabold text-slate-950">{{ number_format($submissionCount) }}</p>
            <p class="mt-2 text-sm text-slate-500">Across all students</p>
        </article>
    </section>

    <section class="grid gap-5 xl:grid-cols-[1.6fr_1fr]">
        <article class="dashboard-panel">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Last 7 days</p>
                    <h2 class="mt-1 font-display text-xl font-bold text-slate-950">Assessment activity</h2>
                </div>
                <span class="rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700">Daily submissions</span>
            </div>
            <div class="mt-5 h-64">
                <canvas data-admin-chart="submissions" data-chart="{{ json_encode(['labels' => $submissionTrend->pluck('label'), 'values' => $submissionTrend->pluck('count')]) }}" role="img" aria-label="Assessment submissions over the last seven days"></canvas>
            </div>
        </article>

        <article class="dashboard-panel">
            <div>
                <p class="text-sm font-semibold text-slate-500">Organization</p>
                <h2 class="mt-1 font-display text-xl font-bold text-slate-950">Students by institute</h2>
            </div>
            @if ($instituteChart->isEmpty())
                <div class="mt-8 rounded-xl border border-dashed border-slate-200 px-4 py-10 text-center text-sm text-slate-500">Create an institute and import students to see enrollment here.</div>
            @else
                <div class="mt-4 h-52">
                    <canvas data-admin-chart="institutes" data-chart="{{ json_encode(['labels' => $instituteChart->pluck('name'), 'values' => $instituteChart->pluck('users_count')]) }}" role="img" aria-label="Student accounts for the largest institutes"></canvas>
                </div>
            @endif
            <a href="{{ route('admin.institutes.index') }}" class="mt-4 inline-flex text-sm font-bold text-indigo-700 hover:text-indigo-900">View all institutes <span class="ml-1" aria-hidden="true">→</span></a>
        </article>
    </section>

    <section id="assessment-activity" class="dashboard-panel scroll-mt-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-slate-500">Latest activity</p>
                <h2 class="mt-1 font-display text-xl font-bold text-slate-950">Recent assessments</h2>
            </div>
            <a href="{{ route('admin.dashboard') }}#assessment-activity" class="text-sm font-bold text-indigo-700 hover:text-indigo-900">View assessment results</a>
        </div>
        <div class="mt-5 overflow-x-auto">
            <table class="admin-data-table min-w-full">
                <thead><tr><th>Assessment</th><th>Created by</th><th>Status</th><th>Submissions</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                    @forelse ($assessments as $assessment)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $assessment->title }}</td>
                            <td>{{ $assessment->author->name }}</td>
                            <td><span @class(['admin-status-badge', 'bg-emerald-50 text-emerald-700' => $assessment->status === 'published', 'bg-slate-100 text-slate-600' => $assessment->status !== 'published'])>{{ str($assessment->status)->title() }}</span></td>
                            <td>{{ number_format($assessment->submissions_count) }}</td>
                            <td class="text-right"><a href="{{ route('assessments.results', $assessment) }}" class="font-bold text-indigo-700 hover:text-indigo-900">View results</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">No assessments have been created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@include('admin.partials.shell-end')
@endsection
