@php
    use App\Http\Middleware\CareEarthAuth;
@endphp

<aside class="admin-sidebar w-52 shrink-0 bg-white border-r border-slate-200 p-4">
    <div>
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">物件売買</p>
        <nav class="space-y-1">
            @if ($canAccessPropertyMaster ?? CareEarthAuth::canAccessPropertyMaster(request()))
                <a
                    href="{{ route('properties.index') }}"
                    class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('properties.index', 'properties.show', 'properties.edit') && request()->query('from') !== 'reference' ? 'is-active' : '' }}"
                >
                    物件マスターデータ一覧
                </a>
            @endif
            <a
                href="{{ route('reference.index') }}"
                class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('reference.*') || (request()->routeIs('properties.show') && request()->query('from') === 'reference') ? 'is-active' : '' }}"
            >
                参照一覧
            </a>
            @if ($canAccessPropertyMaster ?? CareEarthAuth::canAccessPropertyMaster(request()))
                <a
                    href="{{ route('properties.create') }}"
                    class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('properties.create') ? 'is-active' : '' }}"
                >
                    物件売買登録
                </a>
            @endif
            <a
                href="{{ route('property.deal-drafts.index') }}"
                class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('property.deal-drafts.*') ? 'is-active' : '' }}"
            >
                物件データ
            </a>
        </nav>
    </div>
</aside>
