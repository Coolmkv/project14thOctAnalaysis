<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="265px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-3">
            <span class="admin-brand-mark">R</span>
            <span class="d-flex flex-column">
                <span class="text-white fs-5 fw-bold">RIASEC</span>
                <span class="text-gray-500 fs-8">Admin workspace</span>
            </span>
        </a>
        <div id="kt_app_sidebar_toggle" class="app-sidebar-toggle btn btn-icon btn-sm h-30px w-30px position-absolute top-50 start-100 translate-middle rotate" data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body" data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-outline ki-arrow-left fs-2 rotate-180"></i>
        </div>
    </div>
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <div id="kt_app_sidebar_menu" class="menu menu-column menu-rounded menu-sub-indention px-3" data-kt-menu="true" data-kt-menu-expand="false">
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <span class="menu-icon"><i class="ki-outline ki-element-11 fs-2"></i></span>
                        <span class="menu-title">Overview</span>
                    </a>
                </div>
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Management</span></div>
                </div>
                <div class="menu-item {{ request()->routeIs('admin.accounts.*', 'admin.users.*') ? 'here show' : '' }}">
                    <a class="menu-link {{ request()->routeIs('admin.accounts.*', 'admin.users.*') ? 'active' : '' }}" href="{{ route('admin.accounts.create') }}">
                        <span class="menu-icon"><i class="ki-outline ki-profile-user fs-2"></i></span>
                        <span class="menu-title">Accounts</span>
                    </a>
                </div>
                <div class="menu-item {{ request()->routeIs('admin.institutes.*') ? 'here show' : '' }}">
                    <a class="menu-link {{ request()->routeIs('admin.institutes.*') ? 'active' : '' }}" href="{{ route('admin.institutes.index') }}">
                        <span class="menu-icon"><i class="ki-outline ki-office-bag fs-2"></i></span>
                        <span class="menu-title">Institutes</span>
                    </a>
                </div>
                <div class="menu-item {{ request()->routeIs('admin.students.import*') ? 'here show' : '' }}">
                    <a class="menu-link {{ request()->routeIs('admin.students.import*') ? 'active' : '' }}" href="{{ route('admin.students.import-form') }}">
                        <span class="menu-icon"><i class="ki-outline ki-file-up fs-2"></i></span>
                        <span class="menu-title">Import students</span>
                    </a>
                </div>
                <div class="menu-item {{ request()->routeIs('admin.staff.*') ? 'here show' : '' }}">
                    <a class="menu-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}" href="{{ route('admin.staff.index') }}">
                        <span class="menu-icon"><i class="ki-outline ki-people fs-2"></i></span>
                        <span class="menu-title">Staff</span>
                    </a>
                </div>
                <div class="menu-item {{ request()->routeIs('admin.students.index') ? 'here show' : '' }}">
                    <a class="menu-link {{ request()->routeIs('admin.students.index') ? 'active' : '' }}" href="{{ route('admin.students.index') }}">
                        <span class="menu-icon"><i class="ki-outline ki-user-square fs-2"></i></span>
                        <span class="menu-title">Students</span>
                    </a>
                </div>
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Platform</span></div>
                </div>
                <div class="menu-item">
                    <a class="menu-link" href="{{ route('admin.dashboard') }}#assessment-activity">
                        <span class="menu-icon"><i class="ki-outline ki-chart-simple-3 fs-2"></i></span>
                        <span class="menu-title">Assessments</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="app-sidebar-footer flex-column-auto pt-5 pb-7 px-6">
        <div class="rounded p-4 bg-light-primary">
            <div class="text-primary fw-bold fs-7">Career exploration</div>
            <div class="text-gray-600 fs-8 mt-1">Manage your RIASEC platform</div>
        </div>
    </div>
</div>
