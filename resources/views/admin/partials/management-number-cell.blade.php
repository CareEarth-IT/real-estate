@php
    $customer = $customer ?? null;
@endphp
<div class="application-block__cell">
    <span class="application-block__cell-label">管理番号</span>
    <div class="application-block__cell-value">
        @if ($customer?->case_number)
            <a
                href="{{ route('admin.customers.index', ['search' => $customer->case_number]) }}"
                class="text-primary-600 hover:underline font-medium"
                title="顧客一覧で表示"
            >{{ $customer->displayCustomerId() }}</a>
        @else
            <span class="text-slate-400">未登録</span>
        @endif
    </div>
</div>
