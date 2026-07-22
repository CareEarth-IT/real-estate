<div class="application-blocks-board">
    <div class="application-blocks-grid">
        @foreach ($flowManagements as $flowManagement)
            <article
                class="application-block flow-management-summary-card {{ $flowManagement->settlement_transition ? 'has-sticky-highlight-blue' : '' }}"
                data-flow-management-id="{{ $flowManagement->id }}"
                data-detail-url="{{ route('admin.flow-managements.show', $flowManagement) }}"
                tabindex="0"
                role="link"
                aria-label="{{ $flowManagement->property_name ?: '物件名未設定' }}の書類管理詳細を表示"
            >
                <div class="application-block__header">
                    <h3 class="application-block__title">
                        {{ $flowManagement->property_name ?: '（物件名未設定）' }}
                    </h3>
                </div>

                <div class="application-block__body">
                    <div class="application-block__cells">
                        <div class="application-block__cell">
                            <span class="application-block__cell-label">担当者</span>
                            <div class="application-block__cell-value">{{ $flowManagement->staff_in_charge ?: '—' }}</div>
                        </div>
                        <div class="application-block__cell">
                            <span class="application-block__cell-label">契約者</span>
                            <div class="application-block__cell-value">{{ $flowManagement->contractor ?: '—' }}</div>
                        </div>
                        <div class="application-block__cell">
                            <span class="application-block__cell-label">入居日</span>
                            <div class="application-block__cell-value">{{ $flowManagement->move_in_date?->format('Y/m/d') ?? '—' }}</div>
                        </div>
                        <div class="application-block__cell">
                            <span class="application-block__cell-label">来社予定日</span>
                            <div class="application-block__cell-value">{{ $flowManagement->scheduled_visit_date?->format('Y/m/d') ?? '—' }}</div>
                        </div>
                        <div class="application-block__cell">
                            <span class="application-block__cell-label">記入方法</span>
                            <div class="application-block__cell-value">{{ $flowManagement->entry_method ?: '—' }}</div>
                        </div>
                    </div>
                </div>

                <div class="application-block__flags">
                    <label class="application-block__flag flow-check-cell {{ $flowManagement->settlement_transition ? 'admin-highlight-bg' : '' }}">
                        <input
                            type="checkbox"
                            class="flow-field-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                            data-field="settlement_transition"
                            @checked($flowManagement->settlement_transition)
                            @disabled(!($canEdit ?? false))
                        >
                        <span>決済金管理に移行</span>
                    </label>
                    @if ($flowManagement->hasAnyContractDocumentUrl())
                        <span class="self-center text-xs font-semibold text-slate-500" title="詳細で契約書類リンクを確認できます">
                            契約書類あり
                        </span>
                    @endif
                    <span class="ml-auto self-center text-xs font-semibold text-primary-600">詳細を見る →</span>
                </div>
            </article>
        @endforeach
    </div>
</div>

@if ($flowManagements->hasPages())
    <div class="mt-6 pb-2">
        {{ $flowManagements->links('vendor.pagination.admin') }}
    </div>
@endif

@push('scripts')
<script>
    document.querySelectorAll('.flow-management-summary-card[data-detail-url]').forEach((card) => {
        const openDetail = () => {
            window.location.href = card.dataset.detailUrl;
        };

        card.addEventListener('click', (event) => {
            if (event.target.closest('input, button, a, select, textarea, label')) {
                return;
            }
            openDetail();
        });

        card.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openDetail();
            }
        });
    });
</script>
@endpush
