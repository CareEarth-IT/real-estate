@php
    $isMasterSection = request()->routeIs('properties.*', 'reference.*', 'users.*');
@endphp

<aside class="w-52 shrink-0 bg-white border-r border-slate-200 p-4">
    @if ($isMasterSection)
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">物件マスター</p>
        <nav class="space-y-1">
            <a
                href="{{ route('properties.index') }}"
                class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('properties.index') || request()->routeIs('properties.show') || request()->routeIs('properties.edit') ? 'is-active' : '' }}"
            >
                マスターデータ一覧
            </a>
            <a
                href="{{ route('reference.index') }}"
                class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('reference.*') ? 'is-active' : '' }}"
            >
                参照一覧
            </a>
            <a
                href="{{ route('properties.create') }}"
                class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('properties.create') ? 'is-active' : '' }}"
            >
                データ登録
            </a>
            <a
                href="{{ route('customers.create') }}"
                class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium"
                target="_blank"
                rel="noopener"
            >
                賃貸申込フォーム ↗
            </a>
        </nav>
    @else
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">賃貸管理</p>
        <nav class="space-y-1">
            <a
                href="{{ route('admin.applications.index') }}"
                class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.applications.*') ? 'is-active' : '' }}"
            >
                申込一覧
            </a>
            <a
                href="{{ route('admin.screening-completions.index') }}"
                class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.screening-completions.*') ? 'is-active' : '' }}"
            >
                審査完了一覧
            </a>
            <a
                href="{{ route('admin.flow-managements.index') }}"
                class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.flow-managements.*') ? 'is-active' : '' }}"
            >
                フロー管理
            </a>
            <a
                href="{{ route('admin.settlement-managements.index') }}"
                class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.settlement-managements.*') ? 'is-active' : '' }}"
            >
                決済金管理
            </a>
        </nav>
    @endif
</aside>
