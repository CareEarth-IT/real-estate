@php
    use App\Support\PropertyDealDraftFiscalYear;
    use App\Support\PropertyDealDraftPropertyTaxes;
    use App\Support\PropertyDealDraftSheet;

    $previewCaseNumber = old('case_number', $record->case_number);
@endphp

<aside class="deal-draft-edit-layout__preview" aria-label="入力プレビュー">
    <div class="deal-draft-preview">
        <div class="deal-draft-preview__header">
            <h3 class="deal-draft-preview__title">リアルタイムプレビュー</h3>
            <p class="deal-draft-preview__subtitle">一覧表示と同じ形式で反映されます</p>
        </div>

        <div class="deal-draft-preview__sheet-wrapper deal-draft-sheet-wrapper">
            <div class="deal-draft-sheet-scroll">
                <table class="deal-draft-sheet deal-draft-preview__sheet">
                    <thead>
                        <tr>
                            <th class="deal-draft-sheet__label-col" scope="col">項目</th>
                            <th class="deal-draft-sheet__case-col" scope="col">
                                <span data-preview-header-case>{{ $previewCaseNumber ?: '—' }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($formRows as $row)
                            @if (($row['type'] ?? null) === 'group')
                                <tr class="deal-draft-sheet__group-row">
                                    <th class="deal-draft-sheet__label-col" scope="row">
                                        {{ $row['label'] }}
                                        @if (!empty($row['subtitle']))
                                            <span class="deal-draft-group-subtitle">{{ $row['subtitle'] }}</span>
                                        @endif
                                    </th>
                                    <td class="deal-draft-sheet__case-col"></td>
                                </tr>
                                @if (($row['group_key'] ?? null) === 'property_taxes')
                                    @foreach ($visibleFiscalYears as $fiscalYear)
                                        @php
                                            $taxAmount = PropertyDealDraftPropertyTaxes::amountForYear($record, $fiscalYear);
                                        @endphp
                                        <tr class="deal-draft-sheet__data-row">
                                            <th class="deal-draft-sheet__label-col is-indent" scope="row">
                                                {{ PropertyDealDraftFiscalYear::label($fiscalYear) }}
                                            </th>
                                            <td
                                                class="deal-draft-sheet__case-col is-num"
                                                data-preview-field="property_tax"
                                                data-preview-fiscal-year="{{ $fiscalYear }}"
                                            >{{ $taxAmount !== null ? number_format((int) $taxAmount) : '0' }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                                @if (($row['group_key'] ?? null) === 'ad_fees')
                                    <tr class="deal-draft-sheet__data-row deal-draft-sheet__ad-fees-row">
                                        <th class="deal-draft-sheet__label-col is-indent" scope="row">仲介業者名</th>
                                        <td class="deal-draft-sheet__case-col is-ad-fees" data-preview-ad-fees>
                                            @if ($record->relationLoaded('adFees') && $record->adFees->isNotEmpty())
                                                @foreach ($record->adFees as $fee)
                                                    <div class="deal-draft-preview__ad-fee-line">
                                                        {{ $fee->agency_name ?: '—' }}
                                                        @if ($fee->amount)
                                                            <span class="deal-draft-preview__ad-fee-amount">{{ number_format((int) $fee->amount) }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="deal-draft-preview__empty">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @elseif (($row['type'] ?? null) === 'documents')
                                <tr class="deal-draft-sheet__data-row deal-draft-sheet__documents-row">
                                    <th class="deal-draft-sheet__label-col" scope="row">{{ $row['label'] }}</th>
                                    <td class="deal-draft-sheet__case-col is-documents">
                                        <span class="deal-draft-preview__documents-label">書類一覧</span>
                                    </td>
                                </tr>
                            @else
                                @php
                                    $key = $row['key'];
                                    $format = $row['format'] ?? 'text';
                                    $highlight = $row['highlight'] ?? null;
                                    $rowClass = match ($highlight) {
                                        'cost' => 'deal-draft-sheet__highlight-cost',
                                        'price' => 'deal-draft-sheet__highlight-price',
                                        default => '',
                                    };
                                    $isComputed = ! empty($row['computed']);
                                @endphp
                                <tr @class(['deal-draft-sheet__data-row', $rowClass])>
                                    <th
                                        class="deal-draft-sheet__label-col @if(!empty($row['indent'])) is-indent @endif"
                                        scope="row"
                                    >
                                        {{ $row['label'] }}
                                        @if ($isComputed)
                                            <span class="deal-draft-computed-badge">自動</span>
                                        @endif
                                    </th>
                                    <td
                                        @class([
                                            'deal-draft-sheet__case-col',
                                            'is-num' => in_array($format, ['yen', 'yen_signed', 'percent'], true),
                                            'deal-draft-computed-cell' => $isComputed,
                                        ])
                                        data-preview-field="{{ $key }}"
                                        data-preview-format="{{ $format }}"
                                        @if ($isComputed) data-preview-computed="1" @endif
                                    >{{ PropertyDealDraftSheet::formatCell($record, $row) }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</aside>
