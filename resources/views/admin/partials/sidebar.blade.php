<aside id="admin-sidebar" class="admin-sidebar shrink-0 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm lg:sticky lg:top-6 lg:w-64">
    <div class="mb-5 flex items-center justify-between px-2">
        <span class="admin-sidebar-label text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Workspace</span>
        <button id="admin-sidebar-toggle" type="button" aria-label="Collapse sidebar" aria-expanded="true" class="hidden rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900 lg:inline-flex">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/><path d="m9 6 4 6-4 6" class="sidebar-chevron"/></svg>
        </button>
    </div>
    <nav aria-label="Admin navigation" class="grid grid-cols-2 gap-1 lg:flex lg:flex-col">
        <a href="{{ route('admin.dashboard') }}" title="Overview" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.dashboard')])>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/></svg>
            <span class="admin-sidebar-label">Overview</span>
        </a>
        <details class="admin-sidebar-group col-span-2" open>
            <summary title="Management">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v17H6.5A2.5 2.5 0 0 1 4 17.5z"/><path d="M4 17.5A2.5 2.5 0 0 1 6.5 15H20M8 7h8M8 11h6"/></svg>
                <span class="admin-sidebar-label">Management</span>
                <svg class="admin-sidebar-label admin-group-chevron" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m5 7.5 5 5 5-5"/></svg>
            </summary>
            <div class="admin-sidebar-subnav">
                <a href="{{ route('admin.accounts.create') }}" title="Create account" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.accounts.*')])>
                    <span class="admin-subnav-dot"></span><span class="admin-sidebar-label">Create account</span>
                </a>
                <a href="{{ route('admin.institutes.index') }}" title="Institutes" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.institutes.*')])>
                    <span class="admin-subnav-dot"></span><span class="admin-sidebar-label">Institutes</span>
                </a>
                <a href="{{ route('admin.students.import-form') }}" title="Import students" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.students.import*')])>
                    <span class="admin-subnav-dot"></span><span class="admin-sidebar-label">Import students</span>
                </a>
                <a href="{{ route('admin.staff.index') }}" title="Staff" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.staff.*')])>
                    <span class="admin-subnav-dot"></span><span class="admin-sidebar-label">Staff</span>
                </a>
                <a href="{{ route('admin.students.index') }}" title="Students" @class(['admin-nav-link', 'is-active' => request()->routeIs('admin.students.index')])>
                    <span class="admin-subnav-dot"></span><span class="admin-sidebar-label">Students</span>
                </a>
            </div>
        </details>
        <a href="{{ route('admin.dashboard') }}#assessment-activity" title="Assessments" class="admin-nav-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 3.75h8l4 4V20a.75.75 0 0 1-.75.75h-10.5A.75.75 0 0 1 6 20z"/><path d="M14 3.75V8h4M9 12h6M9 16h6"/></svg>
            <span class="admin-sidebar-label">Assessments</span>
        </a>
    </nav>
</aside>
