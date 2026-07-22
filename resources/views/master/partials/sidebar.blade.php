@php
    $navLabels = [
        'applications' => '申込',
        'flow_managements' => '書類管理',
        'customers' => '顧客',
        'settlement_managements' => '決済金管理',
    ];
@endphp

<aside class="master-sidebar admin-sidebar w-52 shrink-0 bg-white border-r border-slate-200 p-4">
    <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">テーブル</p>
    <nav class="space-y-1">
        @foreach ($tables as $key => $table)
            <a
                href="{{ route('master.data.index', array_filter(['table' => $key, 'search' => ($search ?? '') !== '' ? $search : null])) }}"
                class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ $tableKey === $key ? 'is-active' : '' }}"
            >
                {{ $navLabels[$key] ?? $table['label'] }}
            </a>
        @endforeach
    </nav>
</aside>
