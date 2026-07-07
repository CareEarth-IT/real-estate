@php
    use App\Support\PropertyDealDraftFiscalYear;
    use App\Support\PropertyDealDraftPropertyTaxes;

    $visibleYears = $visibleFiscalYears ?? PropertyDealDraftFiscalYear::visibleYears();
    $taxItems = old('property_taxes');

    if ($taxItems === null) {
        $taxItems = [];

        foreach ($visibleYears as $year) {
            $taxItems[] = [
                'fiscal_year' => $year,
                'amount' => PropertyDealDraftPropertyTaxes::amountForYear($record, $year),
            ];
        }
    }
@endphp

<div class="deal-draft-property-taxes-form">
    @foreach ($visibleYears as $index => $year)
        @php
            $amount = $taxItems[$index]['amount'] ?? PropertyDealDraftPropertyTaxes::amountForYear($record, $year);
        @endphp
        <div class="form-group deal-draft-form__field deal-draft-form__field--indent">
            <label for="property_tax_amount_{{ $year }}">{{ PropertyDealDraftFiscalYear::label($year) }}</label>
            <input
                type="hidden"
                name="property_taxes[{{ $index }}][fiscal_year]"
                value="{{ $year }}"
            >
            <input
                type="text"
                inputmode="numeric"
                id="property_tax_amount_{{ $year }}"
                name="property_taxes[{{ $index }}][amount]"
                value="{{ $amount !== null && $amount !== '' ? number_format((int) $amount) : '' }}"
                placeholder="0"
            >
        </div>
    @endforeach
</div>
