@php
    $size = $size ?? 'default';
    $headClass = $size === 'compact'
        ? 'rental-income-contract-table-head rental-income-contract-table-head--compact'
        : 'rental-income-contract-table-head';
@endphp
<thead class="{{ $headClass }}">
    <tr>
        <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[100px]">支払い月</th>
        <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[100px]">契約日</th>
        <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[110px]">家賃</th>
        <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[110px]">入金方法</th>
        <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[130px]">入金状況</th>
        <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[100px]">入居者人数</th>
        <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[90px]">預り金</th>
        <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[110px]">支払日</th>
        @if ($canEdit ?? false)
        <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[140px]"></th>
        @endif
    </tr>
</thead>
