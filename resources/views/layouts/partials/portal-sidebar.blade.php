@php($user = auth()->user())
<div id="kt_app_sidebar" class="app-sidebar flex-column"
     data-kt-drawer="true"
     data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default: true, lg: false}"
     data-kt-drawer-overlay="true"
     data-kt-drawer-width="265px"
     data-kt-drawer-direction="start"
     data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <a href="{{ route('home') }}" class="portal-sidebar-brand d-flex flex-column text-decoration-none" aria-label="{{ config('app.name', 'Mindscape Navigation') }} home">
            <img src="{{ asset('images/mindscape-logo.jpg') }}" alt="{{ config('app.name', 'Mindscape Navigation') }}" class="mindscape-logo-sidebar">
            <span class="d-flex flex-column">
                <span class="text-gray-500 fs-8">
                    @if ($user->hasRole(\App\Models\User::ROLE_USER)) Student space
                    @elseif ($user->hasRole(\App\Models\User::ROLE_COUNSELOR)) Counselor workspace
                    @else Institute workspace
                    @endif
                </span>
            </span>
        </a>
    </div>
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div class="app-sidebar-wrapper">
            <nav id="kt_app_sidebar_menu" class="menu menu-column menu-rounded menu-sub-indention px-3" aria-label="Portal navigation">
                @if ($user->hasRole(\App\Models\User::ROLE_USER))
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('student.assessments.index') ? 'active' : '' }}" href="{{ route('student.assessments.index') }}">
                            <span class="menu-icon"><i class="ki-outline ki-element-11 fs-2"></i></span>
                            <span class="menu-title">Explore assessments</span>
                        </a>
                    </div>
                @elseif ($user->hasRole(\App\Models\User::ROLE_COUNSELOR))
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('assessments.index', 'assessments.create') ? 'active' : '' }}" href="{{ route('assessments.index') }}">
                            <span class="menu-icon"><i class="ki-outline ki-element-11 fs-2"></i></span>
                            <span class="menu-title">Assessment library</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('assessments.create') ? 'active' : '' }}" href="{{ route('assessments.create') }}">
                            <span class="menu-icon"><i class="ki-outline ki-plus-square fs-2"></i></span>
                            <span class="menu-title">Create assessment</span>
                        </a>
                    </div>
                @else
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('group-admin.dashboard') ? 'active' : '' }}" href="{{ route('group-admin.dashboard') }}">
                            <span class="menu-icon"><i class="ki-outline ki-element-11 fs-2"></i></span>
                            <span class="menu-title">Institute dashboard</span>
                        </a>
                    </div>
                @endif
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Your journey</span></div>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="{{ route('home') }}">
                        <span class="menu-icon"><i class="ki-outline ki-home-2 fs-2"></i></span>
                        <span class="menu-title">Career exploration guide</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="{{ route('account.password.edit') }}">
                        <span class="menu-icon"><i class="ki-outline ki-lock-2 fs-2"></i></span>
                        <span class="menu-title">Change password</span>
                    </a>
                </div>
            </nav>
        </div>
    </div>
    <div class="app-sidebar-footer flex-column-auto pt-5 pb-7 px-6">
        <div class="rounded p-4 bg-light-primary">
            <div class="text-primary fw-bold fs-7">Explore with curiosity</div>
            <div class="text-gray-600 fs-8 mt-1">Your interests are a starting point, not a label.</div>
        </div>
    </div>
</div>
