@php
    use App\Support\PropertyDealDraftFiscalYear;
    use App\Support\PropertyDealDraftPropertyTaxes;
    use App\Support\PropertyDealDraftSheet;

    $statusLabels = $statusLabels ?? config('property-deal-draft.statuses', []);
    $propertyTypeLabels = $propertyTypeLabels ?? config('property-deal-draft.property_types', []);
    $visibleFiscalYears = $visibleFiscalYears ?? PropertyDealDraftFiscalYear::visibleYears();
@endphp

<div class="deal-draft-sheet-wrapper">
    <div class="deal-draft-sheet-scroll admin-table-scroll overflow-auto">
        <table class="deal-draft-sheet">
            <thead>
                <tr>
                    <th class="deal-draft-sheet__label-col" scope="col">項目</th>
                    @foreach ($records as $record)
                        <th class="deal-draft-sheet__case-col" scope="col">
                            <div class="deal-draft-sheet__case-header">
                                <span>{{ $record->case_number }}</span>
                                <a href="{{ route('property.deal-drafts.edit', $record) }}" class="deal-draft-sheet__edit-link">編集</a>
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($sheetRows as $row)
                    @if (($row['type'] ?? null) === 'documents')
                        <tr class="deal-draft-sheet__data-row deal-draft-sheet__documents-row">
                            <th class="deal-draft-sheet__label-col" scope="row">{{ $row['label'] }}</th>
                            @foreach ($records as $record)
                                <td class="deal-draft-sheet__case-col is-documents">
                                    <a
                                        href="{{ route('reference.index', ['tab' => 'documents']) }}"
                                        class="btn btn-outline btn-sm deal-draft-documents-link"
                                    >書類一覧</a>
                                </td>
                            @endforeach
                        </tr>
                    @elseif (($row['type'] ?? null) === 'group')
                        <tr class="deal-draft-sheet__group-row">
                            <th class="deal-draft-sheet__label-col" scope="row">
                                {{ $row['label'] }}
                                @if (!empty($row['subtitle']))
                                    <span class="deal-draft-group-subtitle">{{ $row['subtitle'] }}</span>
                                @endif
                            </th>
                            @foreach ($records as $record)
                                <td class="deal-draft-sheet__case-col"></td>
                            @endforeach
                        </tr>
                        @if (($row['group_key'] ?? null) === 'property_taxes')
                            @foreach ($visibleFiscalYears as $fiscalYear)
                                <tr class="deal-draft-sheet__data-row">
                                    <th class="deal-draft-sheet__label-col is-indent" scope="row">
                                        {{ PropertyDealDraftFiscalYear::label($fiscalYear) }}
                                    </th>
                                    @foreach ($records as $record)
                                        @php
                                            $taxAmount = PropertyDealDraftPropertyTaxes::amountForYear($record, $fiscalYear);
                                        @endphp
                                        <td class="deal-draft-sheet__case-col is-num">
                                            {{ $taxAmount !== null ? number_format($taxAmount) : '0' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endif
                        @if (($row['group_key'] ?? null) === 'ad_fees')
                            <tr class="deal-draft-sheet__data-row deal-draft-sheet__ad-fees-row">
                                <th class="deal-draft-sheet__label-col is-indent" scope="row">仲介業者名</th>
                                @foreach ($records as $record)
                                    <td class="deal-draft-sheet__case-col is-ad-fees">
                                        @include('property.deal-drafts._ad-fees-cell', ['record' => $record])
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @else
                        @php
                            $highlight = $row['highlight'] ?? null;
                            $rowClass = match ($highlight) {
                                'cost' => 'deal-draft-sheet__highlight-cost',
                                'price' => 'deal-draft-sheet__highlight-price',
                                default => '',
                            };
                            $format = $row['format'] ?? 'text';
                            $isInlineStatus = $format === 'status';
                            $isInlinePropertyType = $format === 'property_type';
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
                            @foreach ($records as $record)
                                <td
                                    class="deal-draft-sheet__case-col @if(in_array($format, ['yen', 'yen_signed', 'percent'], true)) is-num @endif @if($isInlineStatus || $isInlinePropertyType) is-inline-select @endif @if($isComputed) deal-draft-computed-cell @endif"
                                    @if ($isComputed)
                                        data-computed-field="{{ $row['key'] }}"
                                        data-computed-format="{{ $format }}"
                                        data-deal-draft-id="{{ $record->id }}"
                                    @endif
                                >
                                    @if ($isInlineStatus)
                                        <select
                                            class="deal-draft-inline-field"
                                            data-deal-draft-id="{{ $record->id }}"
                                            data-field="status"
                                            data-label="状況"
                                        >
                                            @foreach ($statusLabels as $value => $label)
                                                <option value="{{ $value }}" @selected($record->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @elseif ($isInlinePropertyType)
                                        <select
                                            class="deal-draft-inline-field"
                                            data-deal-draft-id="{{ $record->id }}"
                                            data-field="property_type"
                                            data-label="種別"
                                        >
                                            <option value="">—</option>
                                            @foreach ($propertyTypeLabels as $value => $label)
                                                <option value="{{ $value }}" @selected($record->property_type === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{ PropertyDealDraftSheet::formatCell($record, $row) }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
