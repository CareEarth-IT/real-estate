@php
    use App\Support\YearMonth;

    $size = $size ?? 'default';
    $rowClass = 'rental-income-row '.$paymentStatusClass($record->payment_status).' align-top transition-colors';

    if ($size === 'compact') {
        $rowClass .= ' rental-income-row--compact';
    } elseif ($size === 'featured') {
        $rowClass .= ' rental-income-row--featured';
    }
@endphp

<tr
    class="{{ $rowClass }}"
    data-rental-income-id="{{ $record->id }}"
    data-payment-status="{{ $record->payment_status ?? 'unpaid' }}"
>
    <td class="px-3 py-3 whitespace-nowrap">{{ YearMonth::formatShort($record->payment_month) }}</td>
    <td class="px-3 py-3 whitespace-nowrap">{{ YearMonth::format($record->rent_year_month) }}</td>
    <td class="px-3 py-3 whitespace-nowrap">{{ $record->rent_amount !== null ? number_format($record->rent_amount) : '0' }}</td>
    <td class="px-3 py-3 whitespace-nowrap">{{ $paymentMethodLabels[$record->payment_method] ?? ($record->payment_method ?: '—') }}</td>
    <td class="px-3 py-3 whitespace-nowrap">
        @if ($canEdit ?? false)
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
    @if ($canEdit ?? false)
    <td class="px-3 py-3 whitespace-nowrap">
        <a href="{{ route('property.rental-income.edit', $record) }}" class="btn btn-outline btn-sm">編集</a>
    </td>
    @endif
</tr>
