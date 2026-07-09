@php
    use App\Support\YearMonth;

    $paymentMonthOptions = $paymentMonthOptions ?? [];
    $activePaymentMonth = $activePaymentMonth ?? null;
@endphp

@if (count($paymentMonthOptions) > 0)
<div class="rental-income-month-picker">
    <label for="paymentMonthInput" class="rental-income-month-picker-label">支払い月</label>
    <div class="rental-income-month-combobox" id="paymentMonthCombobox">
        <input
            type="text"
            id="paymentMonthInput"
            class="rental-income-month-combobox-input"
            value="{{ YearMonth::formatShort($activePaymentMonth) }}"
            placeholder="yy/mmで検索"
            autocomplete="off"
            spellcheck="false"
            role="combobox"
            aria-expanded="false"
            aria-controls="paymentMonthListbox"
            aria-autocomplete="list"
        >
        <input type="hidden" id="paymentMonthSelect" value="{{ $activePaymentMonth }}">
        <ul
            id="paymentMonthListbox"
            class="rental-income-month-combobox-list"
            role="listbox"
            hidden
        >
            @foreach ($paymentMonthOptions as $month)
                <li
                    class="rental-income-month-combobox-option"
                    role="option"
                    data-value="{{ $month }}"
                    data-label="{{ YearMonth::formatShort($month) }}"
                    data-full="{{ YearMonth::format($month) }}"
                    @if ($activePaymentMonth === $month) aria-selected="true" @endif
                >{{ YearMonth::formatShort($month) }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif
