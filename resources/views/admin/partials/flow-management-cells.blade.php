@php
    $flowEditable = $flowEditable ?? false;
@endphp

<td class="flow-section-cell px-3 py-3 whitespace-nowrap {{ $flowEditable ? '' : 'flow-section-disabled' }}">
    <input
        type="date"
        class="flow-date-field rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
        data-field="move_in_date"
        data-label="入居日"
        value="{{ $flowManagement?->move_in_date?->format('Y-m-d') }}"
        @disabled(! $flowEditable)
    >
</td>
<td class="flow-section-cell px-3 py-3 {{ $flowEditable ? '' : 'flow-section-disabled' }}">
        <input
            type="text"
            class="flow-inline-text-field w-full rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
            data-field="document_deadline"
        data-label="書類期日"
        maxlength="255"
        value="{{ $flowManagement?->document_deadline }}"
        @disabled(! $flowEditable)
    >
</td>
<td class="flow-section-cell px-3 py-3 whitespace-nowrap {{ $flowEditable ? '' : 'flow-section-disabled' }}">
    <input
        type="date"
        class="flow-date-field rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
        data-field="scheduled_visit_date"
        data-label="来社予定日"
        value="{{ $flowManagement?->scheduled_visit_date?->format('Y-m-d') }}"
        @disabled(! $flowEditable)
    >
</td>
<td class="flow-section-cell px-3 py-3 whitespace-nowrap {{ $flowEditable ? '' : 'flow-section-disabled' }}">
    <input
        type="date"
        class="flow-date-field rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
        data-field="key_handover_date"
        data-label="鍵渡日"
        value="{{ $flowManagement?->key_handover_date?->format('Y-m-d') }}"
        @disabled(! $flowEditable)
    >
</td>
@foreach ($booleanFields as $field)
    @if (in_array($field, ['settlement_transition', 'has_broker_fee'], true))
        @continue
    @endif
    @if ($field === 'transfer_request_to_applicant')
        <td class="flow-section-cell px-3 py-3 {{ $flowEditable ? '' : 'flow-section-disabled' }}">
            <input
                type="text"
                class="flow-inline-text-field w-full rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                data-field="ad_fee_invoice_creation"
                data-label="広告料請求書作成"
                maxlength="50"
                placeholder="済 / 不要"
                value="{{ $flowManagement?->ad_fee_invoice_creation }}"
                @disabled(! $flowEditable)
            >
        </td>
    @endif
    <td class="flow-section-cell px-3 py-3 text-center flow-check-cell transition-colors {{ $flowEditable && $flowManagement?->{$field} ? 'admin-highlight-bg' : '' }} {{ $flowEditable ? '' : 'flow-section-disabled' }}">
        <input
            type="checkbox"
            class="flow-field-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
            data-field="{{ $field }}"
            @checked($flowManagement?->{$field})
            @disabled(! $flowEditable)
        >
    </td>
@endforeach
<td class="flow-section-cell px-3 py-3 text-center flow-check-cell transition-colors {{ $flowEditable && $flowManagement?->has_broker_fee ? 'admin-highlight-bg' : '' }} {{ $flowEditable ? '' : 'flow-section-disabled' }}">
    <input
        type="checkbox"
        class="flow-field-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
        data-field="has_broker_fee"
        @checked($flowManagement?->has_broker_fee)
        @disabled(! $flowEditable)
    >
</td>
<td class="flow-section-cell px-3 py-3 text-center flow-check-cell transition-colors {{ $flowEditable && $flowManagement?->settlement_transition ? 'admin-highlight-bg' : '' }} {{ $flowEditable ? '' : 'flow-section-disabled' }}">
    <input
        type="checkbox"
        class="flow-field-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
        data-field="settlement_transition"
        @checked($flowManagement?->settlement_transition)
        @disabled(! $flowEditable)
    >
</td>
