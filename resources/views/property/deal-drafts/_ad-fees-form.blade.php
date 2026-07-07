@php
    $adFeeItems = old('ad_fees');

    if ($adFeeItems === null) {
        $adFeeItems = ($record->relationLoaded('adFees') && $record->adFees->isNotEmpty())
            ? $record->adFees->map(static fn ($fee): array => [
                'agency_name' => $fee->agency_name,
                'amount' => $fee->amount,
            ])->all()
            : [];
    }
@endphp

<div class="deal-draft-ad-fees-form" id="dealDraftAdFeesForm">
    <p class="deal-draft-ad-fees-form__hint">仲介業者名と金額を入力してください。行の追加・削除ができます。</p>

    <div class="deal-draft-ad-fees-form__list" id="dealDraftAdFeesList">
        @forelse ($adFeeItems as $index => $fee)
            <div class="deal-draft-ad-fee-form-row">
                <div class="form-group deal-draft-ad-fee-form-row__name">
                    <label for="ad_fee_name_{{ $index }}">仲介業者名</label>
                    <input
                        type="text"
                        id="ad_fee_name_{{ $index }}"
                        name="ad_fees[{{ $index }}][agency_name]"
                        value="{{ $fee['agency_name'] ?? '' }}"
                        placeholder="例: 健美家"
                    >
                </div>
                <div class="form-group deal-draft-ad-fee-form-row__amount">
                    <label for="ad_fee_amount_{{ $index }}">金額</label>
                    <input
                        type="text"
                        inputmode="numeric"
                        id="ad_fee_amount_{{ $index }}"
                        name="ad_fees[{{ $index }}][amount]"
                        value="{{ isset($fee['amount']) && $fee['amount'] !== '' && $fee['amount'] !== null ? number_format((int) $fee['amount']) : '' }}"
                        placeholder="0"
                    >
                </div>
                <button type="button" class="btn btn-ghost btn-sm deal-draft-ad-fee-form-row__remove">削除</button>
            </div>
        @empty
        @endforelse
    </div>

    <button type="button" class="btn btn-outline btn-sm" id="dealDraftAdFeesAdd">+ 仲介業者名を追加</button>
</div>

<template id="dealDraftAdFeeRowTemplate">
    <div class="deal-draft-ad-fee-form-row">
        <div class="form-group deal-draft-ad-fee-form-row__name">
            <label>仲介業者名</label>
            <input
                type="text"
                data-name="ad_fees[__INDEX__][agency_name]"
                placeholder="例: 健美家"
            >
        </div>
        <div class="form-group deal-draft-ad-fee-form-row__amount">
            <label>金額</label>
            <input
                type="text"
                inputmode="numeric"
                data-name="ad_fees[__INDEX__][amount]"
                placeholder="0"
            >
        </div>
        <button type="button" class="btn btn-ghost btn-sm deal-draft-ad-fee-form-row__remove">削除</button>
    </div>
</template>

<script>
(function () {
    const list = document.getElementById('dealDraftAdFeesList');
    const addButton = document.getElementById('dealDraftAdFeesAdd');
    const template = document.getElementById('dealDraftAdFeeRowTemplate');

    if (!list || !addButton || !template) {
        return;
    }

    function nextIndex() {
        return list.querySelectorAll('.deal-draft-ad-fee-form-row').length;
    }

    function bindRemove(row) {
        row.querySelector('.deal-draft-ad-fee-form-row__remove')?.addEventListener('click', () => {
            row.remove();
            reindexRows();
        });
    }

    function reindexRows() {
        list.querySelectorAll('.deal-draft-ad-fee-form-row').forEach((row, index) => {
            const nameInput = row.querySelector('.deal-draft-ad-fee-form-row__name input');
            const amountInput = row.querySelector('.deal-draft-ad-fee-form-row__amount input');
            const nameLabel = row.querySelector('.deal-draft-ad-fee-form-row__name label');
            const amountLabel = row.querySelector('.deal-draft-ad-fee-form-row__amount label');

            if (nameInput) {
                nameInput.name = `ad_fees[${index}][agency_name]`;
                nameInput.id = `ad_fee_name_${index}`;
            }

            if (amountInput) {
                amountInput.name = `ad_fees[${index}][amount]`;
                amountInput.id = `ad_fee_amount_${index}`;
            }

            if (nameLabel && nameInput) {
                nameLabel.setAttribute('for', nameInput.id);
            }

            if (amountLabel && amountInput) {
                amountLabel.setAttribute('for', amountInput.id);
            }
        });
    }

    function addRow() {
        const index = nextIndex();
        const html = template.innerHTML.replaceAll('__INDEX__', String(index));
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const row = wrapper.firstElementChild;
        list.appendChild(row);
        bindRemove(row);
        reindexRows();
        row.querySelector('input')?.focus();
    }

    list.querySelectorAll('.deal-draft-ad-fee-form-row').forEach(bindRemove);

    addButton.addEventListener('click', addRow);
})();
</script>
