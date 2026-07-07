<div class="deal-draft-ad-fees-cell" data-deal-draft-id="{{ $record->id }}">
  <p class="deal-draft-ad-fees-cell__hint">仲介業者名・金額を入力（自動保存）</p>
  <div class="deal-draft-ad-fees-cell__list">
    @foreach ($record->adFees as $adFee)
      <div class="deal-draft-ad-fee-item" data-ad-fee-id="{{ $adFee->id }}">
        <input
          type="text"
          class="deal-draft-ad-fee-name"
          value="{{ $adFee->agency_name }}"
          placeholder="仲介業者名"
          data-label="仲介業者名"
        >
        <input
          type="text"
          inputmode="numeric"
          class="deal-draft-ad-fee-amount"
          value="{{ $adFee->amount !== null ? number_format($adFee->amount) : '' }}"
          placeholder="金額"
          data-label="金額"
        >
        <button type="button" class="deal-draft-ad-fee-delete btn btn-ghost btn-sm">削除</button>
      </div>
    @endforeach
  </div>
  <button type="button" class="deal-draft-ad-fee-add btn btn-outline btn-sm">+ 追加</button>
</div>
