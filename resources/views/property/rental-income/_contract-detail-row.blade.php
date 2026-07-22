@php
    use App\Support\YearMonth;

    $size = $size ?? 'default';
    $showTerminateAction = ($showTerminateAction ?? false)
        && ($canEdit ?? false)
        && ($termination ?? null) === null;
    $rowClass = 'rental-income-row '.$paymentStatusClass($record->payment_status).' align-top transition-colors';
    $terminationMonth = \App\Support\PropertyRentalIncomeContract::terminationCutoffMonth($termination ?? null);
    $isTerminationMonth = $terminationMonth !== null
        && (int) ($record->payment_month ?? 0) === $terminationMonth;
    $showTerminationDateNote = ($showTerminationDateNote ?? true)
        && $isTerminationMonth
        && isset($termination)
        && ($termination->terminated_on || $termination->terminated_at);
    $defaultTerminatedOn = $record->payment_on?->format('Y-m-d')
        ?? (YearMonth::isValid((int) $record->payment_month)
            ? YearMonth::lastDay((int) $record->payment_month)
            : now()->toDateString());

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
    <td class="px-3 py-3 whitespace-nowrap">
        <div>{{ YearMonth::formatShort($record->payment_month) }}</div>
        @if ($showTerminationDateNote)
            <div class="rental-income-termination-date-note">
                解約日{{ ($termination->terminated_on ?? $termination->terminated_at)->format('n月j日') }}
            </div>
        @endif
    </td>
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
        <div class="flex items-center gap-2">
            <a href="{{ route('property.rental-income.edit', $record) }}" class="btn btn-outline btn-sm">編集</a>
            @if ($showTerminateAction)
                <button
                    type="button"
                    class="btn btn-outline btn-sm rental-income-terminate-open"
                    data-terminated-on="{{ $defaultTerminatedOn }}"
                    data-payment-month-label="{{ YearMonth::formatShort($record->payment_month) }}"
                >
                    解約
                </button>
            @endif
        </div>
    </td>
    @endif
</tr>
