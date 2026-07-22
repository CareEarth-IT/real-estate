<div class="application-blocks-board">
    <div class="application-blocks-grid">
        @foreach ($settlementManagements as $settlementManagement)
            <article
                class="application-block settlement-management-summary-card"
                data-settlement-management-id="{{ $settlementManagement->id }}"
                data-detail-url="{{ route('admin.settlement-managements.show', $settlementManagement) }}"
                tabindex="0"
                role="link"
                aria-label="{{ $settlementManagement->property_name ?: '物件名未設定' }}の決済金管理詳細を表示"
            >
                <div class="application-block__header">
                    <h3 class="application-block__title">
                        {{ $settlementManagement->property_name ?: '（物件名未設定）' }}
                    </h3>
                    <div class="flex flex-wrap gap-1">
                        @forelse ($settlementManagement->feeTypeBadges() as $badge)
                            <span class="inline-flex items-center rounded-md border px-2.5 py-1 text-xs font-semibold {{ $badge['classes'] }}">
                                {{ $badge['label'] }}
                            </span>
                        @empty
                        @endforelse
                    </div>
                </div>

                <div class="application-block__body">
                    <div class="application-block__cells">
                        <div class="application-block__cell">
                            <span class="application-block__cell-label">担当者</span>
                            <div class="application-block__cell-value">{{ $settlementManagement->staff_in_charge ?: '—' }}</div>
                        </div>
                        <div class="application-block__cell">
                            <span class="application-block__cell-label">物件名</span>
                            <div class="application-block__cell-value">{{ $settlementManagement->property_name ?: '—' }}</div>
                        </div>
                        <div class="application-block__cell">
                            <span class="application-block__cell-label">契約日</span>
                            <div class="application-block__cell-value">{{ $settlementManagement->contract_date?->format('Y/m/d') ?? '—' }}</div>
                        </div>
                        <div class="application-block__cell">
                            <span class="application-block__cell-label">決済振込日</span>
                            <div class="application-block__cell-value">{{ $settlementManagement->settlement_transfer_date?->format('Y/m/d') ?? '—' }}</div>
                        </div>
                    </div>
                </div>

                <div class="application-block__flags">
                    @foreach (['offset_statement_printing' => '明細書印刷', 'individual_invoice_printing' => '請求書印刷'] as $field => $label)
                        <label class="application-block__flag settlement-check-cell {{ $settlementManagement->{$field} ? 'admin-highlight-bg' : '' }}">
                            <input
                                type="checkbox"
                                class="settlement-summary-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                data-field="{{ $field }}"
                                @checked($settlementManagement->{$field})
                                @disabled(!($canEdit ?? false))
                            >
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                    <span class="ml-auto self-center text-xs font-semibold text-primary-600">詳細を見る →</span>
                </div>
            </article>
        @endforeach
    </div>
</div>

@if ($settlementManagements->hasPages())
    <div class="mt-6 pb-2">
        {{ $settlementManagements->links('vendor.pagination.admin') }}
    </div>
@endif

@push('scripts')
<script>
    document.querySelectorAll('.settlement-management-summary-card[data-detail-url]').forEach((card) => {
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

    document.querySelectorAll('.settlement-summary-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', async () => {
            const card = checkbox.closest('[data-settlement-management-id]');
            const previous = !checkbox.checked;
            checkbox.closest('.settlement-check-cell')?.classList.toggle('admin-highlight-bg', checkbox.checked);

            try {
                const response = await fetch(adminApiUrl(`/admin/settlement-managements/${card.dataset.settlementManagementId}/fields`), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        field: checkbox.dataset.field,
                        value: checkbox.checked ? 1 : 0,
                    }),
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.message || '更新に失敗しました。');
                }
            } catch (error) {
                checkbox.checked = previous;
                checkbox.closest('.settlement-check-cell')?.classList.toggle('admin-highlight-bg', checkbox.checked);
                alert(error.message);
            }
        });
    });
</script>
@endpush
