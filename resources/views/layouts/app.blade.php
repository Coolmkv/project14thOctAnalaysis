<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pathfinder') · Pathfinder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f8fc] text-slate-800 antialiased">
    <header class="border-b border-slate-200/80 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8">
            <a href="{{ auth()->check() ? route('assessments.index') : route('login') }}" class="flex items-center gap-3 font-bold text-slate-900">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-lg text-white">P</span>
                <span class="font-display text-lg">pathfinder<span class="text-indigo-600">.</span></span>
            </a>
            @auth
                <div class="flex items-center gap-4 text-sm">
                    <span class="hidden text-slate-500 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg px-3 py-2 font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900">Sign out</button>
                    </form>
                </div>
            @endauth
        </div>
    </header>
    <main class="mx-auto max-w-7xl px-5 py-10 lg:px-8 lg:py-14">
        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>