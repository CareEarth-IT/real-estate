<aside class="admin-sidebar w-52 shrink-0 bg-white border-r border-slate-200 p-4">
    <div class="mb-6">
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">物件売買</p>
        <nav class="space-y-1">
            <a
                href="{{ route('properties.index') }}"
                class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('properties.index', 'properties.show', 'properties.edit') ? 'is-active' : '' }}"
            >
                物件マスターデータ一覧
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
                物件売買登録
            </a>
        </nav>
    </div>

    <div>
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">家賃収入</p>
        <nav class="space-y-1">
            <a
                href="{{ route('property.rental-income.index') }}"
                class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('property.rental-income.*') ? 'is-active' : '' }}"
            >
                家賃収入データ
            </a>
        </nav>
    </div>
</aside>
