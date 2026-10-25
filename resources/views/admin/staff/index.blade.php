@extends('layouts.admin')

@section('title', 'Staff accounts')
@section('page-title', 'Staff accounts')
@section('breadcrumb', 'Directory')

@section('content')
@include('admin.partials.shell-start')
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Directory</p>
            <h1 class="mt-1 font-display text-3xl font-extrabold tracking-tight text-slate-950">Staff accounts</h1>
            <p class="mt-2 text-slate-500">Search administrators, counselors, and group admins.</p>
        </div>
        <a href="{{ route('admin.accounts.create') }}" class="rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white hover:bg-indigo-700">Create account</a>
    </header>

    <section class="dashboard-panel">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="font-display text-xl font-bold text-slate-950">All staff</h2>
            <span class="rounded-full bg-indigo-50 px-3 py-1.5 text-sm font-bold text-indigo-700">{{ number_format($staffCount) }} accounts</span>
        </div>
        <div class="overflow-x-auto">
            <table data-datatable data-source="{{ route('admin.data.staff') }}" class="admin-data-table min-w-full">
                <thead><tr><th data-column="name">Name</th><th data-column="email">Email</th><th data-column="role">Role</th><th data-column="institutes" data-sortable="false">Assigned institutes</th></tr></thead>
            </table>
        </div>
    </section>
@include('admin.partials.shell-end')
@endsection
