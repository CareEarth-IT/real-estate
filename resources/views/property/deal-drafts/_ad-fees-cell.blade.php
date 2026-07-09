@php
    $canEdit = $canEdit ?? false;
@endphp
<div class="deal-draft-ad-fees-cell" data-deal-draft-id="{{ $record->id }}">
  @if ($canEdit)
  <p class="deal-draft-ad-fees-cell__hint">仲介業者名・金額を入力（自動保存）</p>
  @endif
  <div class="deal-draft-ad-fees-cell__list">
    @forelse ($record->adFees as $adFee)
      @if ($canEdit)
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
      @else
      <div class="deal-draft-ad-fee-item deal-draft-ad-fee-item--readonly">
        <span>{{ $adFee->agency_name ?: '—' }}</span>
        <span class="is-num">{{ $adFee->amount !== null ? number_format($adFee->amount) : '0' }}</span>
      </div>
      @endif
    @empty
      <span class="text-slate-400">—</span>
    @endforelse
  </div>
  @if ($canEdit)
  <button type="button" class="deal-draft-ad-fee-add btn btn-outline btn-sm">+ 追加</button>
  @endif
</div>
