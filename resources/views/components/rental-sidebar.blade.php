<aside class="admin-sidebar w-52 shrink-0 bg-white border-r border-slate-200 p-4">
    <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">賃貸管理情報</p>
    <nav class="space-y-1">
        <a
            href="{{ route('home') }}"
            class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('home') ? 'is-active' : '' }}"
        >
            ホーム
        </a>
        <a
            href="{{ route('admin.applications.index') }}"
            class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.applications.*') ? 'is-active' : '' }}"
        >
            申込一覧
        </a>
        <a
            href="{{ route('admin.flow-managements.index') }}"
            class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.flow-managements.*') ? 'is-active' : '' }}"
        >
            書類管理
        </a>
        <a
            href="{{ route('admin.settlement-managements.index') }}"
            class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.settlement-managements.*') ? 'is-active' : '' }}"
        >
            決済金管理
        </a>
    </nav>
</aside>
