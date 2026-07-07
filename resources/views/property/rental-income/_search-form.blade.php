@php
    $listRoute = $listRoute ?? 'property.rental-income.index';
    $listParams = $listParams ?? [];
    $paymentStatusLabels = $paymentStatusLabels ?? config('property-rental-income.payment_statuses', []);
    $clearParams = collect($listParams)->except(['search', 'payment_status'])->all();
    $clearUrl = \App\Support\PropertyRentalIncomeListQuery::listUrl($listRoute, $clearParams);
    $hasActiveFilters = ($search ?? '') !== '' || ($paymentStatus ?? null) !== null;
@endphp

<form
    method="GET"
    action="{{ route($listRoute) }}"
    class="rental-income-search-form flex flex-col sm:flex-row sm:items-end gap-2 w-full sm:flex-wrap"
>
    @foreach ($listParams as $name => $value)
        @if ($value !== null && $value !== '' && ! in_array($name, ['search', 'payment_status'], true))
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="rental-income-search-field w-full sm:flex-1 sm:min-w-[220px]">
        <label for="rentalIncomeSearch" class="rental-income-month-picker-label">キーワード検索</label>
        <input
            type="search"
            id="rentalIncomeSearch"
            name="search"
            value="{{ $search }}"
            placeholder="契約者・物件で検索"
            class="rental-income-month-picker-select w-full"
        >
    </div>

    <div class="rental-income-search-field w-full sm:w-auto sm:min-w-[160px]">
        <label for="rentalIncomePaymentStatus" class="rental-income-month-picker-label">入金状況</label>
        <select
            id="rentalIncomePaymentStatus"
            name="payment_status"
            class="rental-income-month-picker-select w-full"
        >
            <option value="">すべて</option>
            @foreach ($paymentStatusLabels as $value => $label)
                <option value="{{ $value }}" @selected(($paymentStatus ?? null) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex shrink-0 gap-2">
        <button type="submit" class="btn btn-outline btn-sm">絞り込み</button>
        @if ($hasActiveFilters)
            <a href="{{ $clearUrl }}" class="btn btn-ghost btn-sm">クリア</a>
        @endif
    </div>
</form>
