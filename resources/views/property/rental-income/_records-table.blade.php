@php
    use App\Support\PropertyRentalIncomeListSort;
    use App\Support\YearMonth;

    $paymentStatusClass = fn (?string $status): string => 'rental-income-status-' . ($status ?: 'unpaid');
    $showPaymentMonthColumn = $showPaymentMonthColumn ?? false;
    $sortableColumns = $sortableColumns ?? null;
    $sort = $sort ?? null;
    $sortDirection = $sortDirection ?? 'desc';
    $listRoute = $listRoute ?? 'property.rental-income.index';
    $listParams = $listParams ?? [];
    $canEdit = $canEdit ?? false;

    $sortHeader = function (string $column, string $label) use ($sortableColumns, $sort, $sortDirection, $listRoute, $listParams): string {
        if ($sortableColumns === null || ! isset($sortableColumns[$column])) {
            return e($label);
        }

        $isActive = $sort === $column;
        $indicator = $isActive ? ($sortDirection === 'asc' ? ' ↑' : ' ↓') : '';
        $url = PropertyRentalIncomeListSort::headerUrl($listRoute, $listParams, $column, $sort, $sortDirection);

        return '<a href="'.e($url).'" class="rental-income-sort-link'.($isActive ? ' is-active' : '').'">'
            .e($label).'<span class="rental-income-sort-indicator">'.$indicator.'</span></a>';
    };
@endphp

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="admin-table-scroll overflow-x-auto">
        <table class="admin-table-sticky rental-income-table min-w-full text-sm text-left">
            <thead class="bg-slate-100 text-slate-700">
                <tr>
                    <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[110px]">{!! $sortHeader('created_on', '作成日') !!}</th>
                    <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[120px]">{!! $sortHeader('contractor', '契約者') !!}</th>
                    <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[140px]">物件</th>
                    @if ($showPaymentMonthColumn)
                        <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[100px]">{!! $sortHeader('payment_month', '支払い月') !!}</th>
                    @endif
                    <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[100px]">契約日</th>
                    <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[110px]">入金方法</th>
                    <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[110px]">家賃</th>
                    <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[130px]">入金状況</th>
                    <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[100px]">入居者人数</th>
                    <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[90px]">預り金</th>
                    <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[110px]">支払日</th>
                    @if ($canEdit)
                    <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[200px]"></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    <tr
                        class="rental-income-row {{ $paymentStatusClass($record->payment_status) }} align-top transition-colors"
                        data-rental-income-id="{{ $record->id }}"
                        data-payment-status="{{ $record->payment_status ?? 'unpaid' }}"
                    >
                        <td class="px-3 py-3 whitespace-nowrap">{{ $record->created_on?->format('Y/m/d') ?? '—' }}</td>
                        <td class="px-3 py-3">{{ $record->contractor ?: '—' }}</td>
                        <td class="px-3 py-3">{{ $record->property_name ?: '—' }}</td>
                        @if ($showPaymentMonthColumn)
                            <td class="px-3 py-3 whitespace-nowrap">{{ YearMonth::formatShort($record->payment_month) }}</td>
                        @endif
                        <td class="px-3 py-3 whitespace-nowrap">{{ YearMonth::format($record->rent_year_month) }}</td>
                        <td class="px-3 py-3 whitespace-nowrap">{{ $paymentMethodLabels[$record->payment_method] ?? ($record->payment_method ?: '—') }}</td>
                        <td class="px-3 py-3 whitespace-nowrap">{{ $record->rent_amount !== null ? number_format($record->rent_amount) : '0' }}</td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            @if ($canEdit)
                            <select
                                class="rental-income-inline-field rental-income-status-field w-full min-w-[120px] rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                data-field="payment_status"
                                data-label="入金状況"
                            >
                                @foreach ($paymentStatusLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(($record->payment_status ?? 'unpaid') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @else
                                {{ $paymentStatusLabels[$record->payment_status ?? 'unpaid'] ?? '—' }}
                            @endif
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">{{ $record->occupant_count ?? '—' }}</td>
                        <td class="px-3 py-3 whitespace-nowrap">{{ $record->deposit_amount !== null ? number_format($record->deposit_amount) : '0' }}</td>
                        <td class="px-3 py-3 whitespace-nowrap">{{ $record->payment_on?->format('Y/m/d') ?? '—' }}</td>
                        @if ($canEdit)
                        <td class="px-3 py-3 whitespace-nowrap">
                            @php
                                $deleteRedirect = route($listRoute ?? 'property.rental-income.index', $listParams ?? []);
                            @endphp
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('property.rental-income.edit', $record) }}" class="btn btn-outline btn-sm">編集</a>
                                @unless ($record->nextMonthCopy)
                                <form
                                    method="post"
                                    action="{{ route('property.rental-income.copy-to-next-month', $record) }}"
                                    class="inline"
                                >
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm">次月へ</button>
                                </form>
                                @endunless
                                <form
                                    method="post"
                                    action="{{ route('property.rental-income.destroy', $record) }}"
                                    class="inline"
                                    onsubmit="return confirm('この月別家賃収入データを削除しますか？');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="redirect" value="{{ $deleteRedirect }}">
                                    <button type="submit" class="btn btn-ghost btn-sm text-red-600">削除</button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
