<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Mindscape Navigation')) · {{ config('app.name', 'Mindscape Navigation') }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/mindscape-logo.jpg') }}">
    <link rel="stylesheet" href="{{ asset('css/metronic-theme.css') }}">
    @vite(['resources/css/app.css', 'resources/css/metronic-admin.css'])
</head>
<body id="kt_app_body"
      data-kt-app-layout="dark-sidebar"
      data-kt-app-header-fixed="true"
      data-kt-app-sidebar-enabled="true"
      data-kt-app-sidebar-fixed="true"
      data-kt-app-sidebar-hoverable="true"
      data-kt-app-sidebar-push-header="true"
      data-kt-app-sidebar-push-toolbar="true"
      data-kt-app-sidebar-push-footer="true"
      data-kt-app-toolbar-enabled="true"
      class="app-default antialiased">
    @auth
        <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
            <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
                <header id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize" data-kt-sticky-offset="{default: '200px', lg: '0'}">
                    <div class="app-container container-fluid d-flex align-items-center justify-content-between" id="kt_app_header_container">
                        <div class="d-flex align-items-center gap-3">
                            <button type="button" class="btn btn-icon btn-active-color-primary w-35px h-35px d-lg-none" id="kt_app_sidebar_mobile_toggle" aria-label="Open navigation">
                                <i class="ki-outline ki-abstract-14 fs-2"></i>
                            </button>
                            <a href="{{ route('home') }}" class="d-flex align-items-center text-decoration-none" aria-label="{{ config('app.name', 'Mindscape Navigation') }} home">
                                <img src="{{ asset('images/mindscape-logo.jpg') }}" alt="{{ config('app.name', 'Mindscape Navigation') }}" class="mindscape-logo-header">
                            </a>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            @include('layouts.partials.theme-mode-switcher')
                            <div class="d-none d-sm-flex flex-column text-end">
                                <span class="text-gray-800 fs-7 fw-bold">{{ auth()->user()->name }}</span>
                                <span class="text-gray-500 fs-8">
                                    @if (auth()->user()->hasRole(\App\Models\User::ROLE_USER)) Student
                                    @elseif (auth()->user()->hasRole(\App\Models\User::ROLE_COUNSELOR)) Counselor
                                    @elseif (auth()->user()->hasRole(\App\Models\User::ROLE_GROUP_ADMIN)) Group admin
                                    @else Administrator
                                    @endif
                                </span>
                            </div>
                            <div class="symbol symbol-40px symbol-circle">
                                <span class="symbol-label bg-light-primary text-primary fs-5 fw-bold">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-sm btn-light-danger">Sign out</button>
                            </form>
                        </div>
                    </div>
                </header>
                <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                    @include('layouts.partials.portal-sidebar')
                    <main class="app-main flex-column flex-row-fluid" id="kt_app_main">
                        <div class="d-flex flex-column flex-column-fluid">
                            <div class="app-toolbar py-3 py-lg-6" id="kt_app_toolbar">
                                <div class="app-container container-fluid d-flex flex-stack">
                                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                                        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">@yield('page-title', trim($__env->yieldContent('title', 'Workspace')))</h1>
                                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                                            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-muted text-hover-primary">Home</a></li>
                                            @hasSection('breadcrumb')
                                                <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                                                <li class="breadcrumb-item text-muted">@yield('breadcrumb')</li>
                                            @endif
                                        </ul>
                                    </div>
                                    @yield('toolbar')
                                </div>
                            </div>
                            <div class="app-content flex-column-fluid" id="kt_app_content">
                                <div class="app-container container-fluid" id="kt_app_content_container">
                                    @if (session('status'))
                                        <div class="alert alert-success d-flex align-items-center p-5 mb-6" role="status">
                                            <i class="ki-outline ki-check-circle fs-2hx text-success me-4"></i>
                                            <div class="d-flex flex-column">{{ session('status') }}</div>
                                        </div>
                                    @endif
                                    @yield('content')
                                </div>
                            </div>
                        </div>
                        <footer class="app-footer">
                            <div class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3">
                                <span class="text-gray-600 fs-7">Career exploration · {{ now()->year }}</span>
                                <a href="{{ route('home') }}" class="text-gray-600 text-hover-primary fs-7">View website</a>
                            </div>
                        </footer>
                    </main>
                </div>
            </div>
        </div>
    @else
        <div class="d-flex flex-column flex-root">
            <header class="bg-white border-bottom">
                <div class="container d-flex align-items-center justify-content-between py-4">
                    <a href="{{ route('home') }}" class="d-flex align-items-center text-gray-900 text-decoration-none fw-bold" aria-label="{{ config('app.name', 'Mindscape Navigation') }} home">
                        <img src="{{ asset('images/mindscape-logo.jpg') }}" alt="{{ config('app.name', 'Mindscape Navigation') }}" class="mindscape-logo-guest">
                    </a>
                    <nav class="d-flex align-items-center gap-3" aria-label="Main navigation">
                        @if (request()->routeIs('home'))
                            <a href="{{ route('login') }}" class="btn btn-sm btn-light-primary">Student sign in</a>
                            <a href="{{ route('register') }}" class="btn btn-sm btn-primary d-none d-sm-inline-flex">Create account</a>
                        @elseif (request()->routeIs('register'))
                            <a href="{{ route('login') }}" class="btn btn-sm btn-light-primary">Sign in</a>
                        @else
                            <a href="{{ route('home') }}" class="btn btn-sm btn-light-primary">Back to website</a>
                        @endif
                    </nav>
                </div>
            </header>
            <main class="container py-8 py-lg-12">
                @if (session('status'))
                    <div class="alert alert-success mb-6" role="status">{{ session('status') }}</div>
                @endif
                @yield('content')
            </main>
            <footer class="border-top bg-white mt-auto">
                <div class="container py-5 d-flex flex-column flex-sm-row justify-content-between gap-3 text-gray-600 fs-7">
                    <span>Career exploration is a process of learning about yourself and your options.</span>
                    <span>RIASEC themes are for reflection—not diagnosis or prediction.</span>
                </div>
            </footer>
        </div>
    @endauth
    <script src="{{ asset('metronic/assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('metronic/assets/js/scripts.bundle.js') }}"></script>
    @vite('resources/js/app.js')
</body>
</html>
