@extends('layouts.admin')

@section('title', '書類管理詳細 — ' . config('app.name'))

@section('content')
<div data-flow-management-id="{{ $flowManagement->id }}">
    <div class="mb-6">
        <a href="{{ route('admin.flow-managements.index') }}" class="inline-flex text-sm text-primary-600 hover:underline mb-4">
            ← 書類管理へ戻る
        </a>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">書類管理詳細</h2>
                <p class="mt-2 text-sm text-slate-600">
                    <strong class="text-slate-900">{{ $flowManagement->property_name ?: '（物件名未設定）' }}</strong>
                    @if ($flowManagement->room_number)
                        <span class="text-slate-400 mx-1">|</span>
                        {{ $flowManagement->room_number }}
                    @endif
                </p>
            </div>

            <label class="inline-flex items-center gap-3 rounded-xl border-2 border-primary-300 bg-primary-50 px-4 py-3 font-semibold text-primary-800 shadow-sm">
                <input
                    type="checkbox"
                    class="flow-detail-checkbox h-5 w-5 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                    data-field="settlement_transition"
                    @checked($flowManagement->settlement_transition)
                    @disabled(!($canEdit ?? false))
                >
                <span>{{ $columnLabels['settlement_transition'] }}</span>
            </label>
        </div>
    </div>

    <div class="application-blocks-board">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
            <div class="application-block__cell">
                <span class="application-block__cell-label">作成日時</span>
                <div class="application-block__cell-value">{{ $flowManagement->application?->created_at?->format('Y/m/d H:i') ?? '—' }}</div>
            </div>
            <div class="application-block__cell">
                <span class="application-block__cell-label">{{ $columnLabels['staff_in_charge'] }}</span>
                <div class="application-block__cell-value">{{ $flowManagement->staff_in_charge ?: '—' }}</div>
            </div>
            <div class="application-block__cell">
                <span class="application-block__cell-label">{{ $columnLabels['contractor'] }}</span>
                <div class="application-block__cell-value">{{ $flowManagement->contractor ?: '—' }}</div>
            </div>
            <div class="application-block__cell">
                <span class="application-block__cell-label">{{ $columnLabels['contractor_furigana'] }}</span>
                <div class="application-block__cell-value">{{ $flowManagement->contractor_furigana ?: '—' }}</div>
            </div>
            <div class="application-block__cell">
                <span class="application-block__cell-label">{{ $columnLabels['contractor_english_name'] }}</span>
                <div class="application-block__cell-value">{{ $flowManagement->contractor_english_name ?: '—' }}</div>
            </div>
            <div class="application-block__cell md:col-span-2 xl:col-span-3">
                <span class="application-block__cell-label">{{ $columnLabels['overseas_screening'] }}</span>
                <div class="application-block__cell-value whitespace-pre-wrap">{{ $flowManagement->overseas_screening ?: '—' }}</div>
            </div>
            <div class="application-block__cell md:col-span-2 xl:col-span-3" data-contract-documents-block>
                <span class="application-block__cell-label">契約書類</span>
                <div class="application-block__cell-value space-y-3">
                    <p class="text-xs font-normal text-slate-500">リンク未入力でも問題ありません。Driveの共有権限があるユーザーのみ閲覧できます。</p>
                    @foreach (\App\Models\FlowManagement::contractDocumentFields() as $field => $label)
                        @php
                            $url = $flowManagement->{$field};
                            $confirmedField = \App\Models\FlowManagement::contractDocumentConfirmedField($field);
                            $isConfirmed = (bool) $flowManagement->{$confirmedField};
                            $status = $flowManagement->contractDocumentStatus($field);
                        @endphp
                        <div
                            class="rounded-lg border border-slate-200 bg-slate-50/70 p-3"
                            data-contract-doc-item
                            data-field="{{ $field }}"
                            data-confirmed-field="{{ $confirmedField }}"
                        >
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <div class="text-sm font-semibold text-slate-800">{{ $label }}</div>
                                <span
                                    data-contract-doc-status
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $status === '完了' ? 'bg-emerald-100 text-emerald-800' : ($status === '確認中' ? 'bg-amber-100 text-amber-900' : 'hidden bg-slate-100 text-slate-500') }}"
                                >{{ $status !== '' ? $status : '未設定' }}</span>
                            </div>
                            <label class="block" data-contract-doc-url-wrap @class(['hidden' => $isConfirmed])>
                                <input
                                    type="url"
                                    class="flow-detail-field application-inline-field"
                                    data-field="{{ $field }}"
                                    data-label="{{ $label }}"
                                    placeholder="リンクを貼り付け（任意）"
                                    value="{{ $url }}"
                                    @readonly(!($canEdit ?? false) || $isConfirmed)
                                >
                            </label>
                            <p
                                data-contract-doc-url-locked
                                class="mt-1 break-all text-sm text-slate-600 {{ $isConfirmed && $url ? '' : 'hidden' }}"
                            >{{ $url }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-3">
                                <a
                                    href="{{ $url ?: '#' }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    data-contract-doc-open
                                    class="inline-flex text-sm font-semibold text-primary-600 hover:underline {{ $url ? '' : 'pointer-events-none opacity-40' }}"
                                    @if (! $url) aria-disabled="true" @endif
                                >
                                    リンクを開く
                                </a>
                                <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 {{ $url ? '' : 'opacity-40' }}">
                                    <input
                                        type="checkbox"
                                        class="contract-doc-confirm-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        data-field="{{ $confirmedField }}"
                                        @checked($isConfirmed)
                                        @disabled(!($canEdit ?? false) || ! $url)
                                    >
                                    <span>確認完了</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="application-block__cell">
                <span class="application-block__cell-label">{{ $columnLabels['property_name'] }}</span>
                <div class="application-block__cell-value">{{ $flowManagement->property_name ?: '—' }}</div>
            </div>
            <div class="application-block__cell">
                <span class="application-block__cell-label">{{ $columnLabels['room_number'] }}</span>
                <div class="application-block__cell-value">{{ $flowManagement->room_number ?: '—' }}</div>
            </div>
            <div class="application-block__cell">
                <span class="application-block__cell-label">{{ $columnLabels['application_method'] }}</span>
                <div class="application-block__cell-value">{{ $flowManagement->application_method ?: '—' }}</div>
            </div>
            <div class="application-block__cell">
                <span class="application-block__cell-label">{{ $columnLabels['entry_method'] }}</span>
                <div class="application-block__cell-value">{{ $flowManagement->entry_method ?: '—' }}</div>
            </div>

            <label class="application-block__cell application-block__cell--editable md:col-span-2 xl:col-span-3">
                <span class="application-block__cell-label">{{ $columnLabels['memo'] }}</span>
                <textarea
                    class="flow-detail-field application-inline-field"
                    rows="3"
                    maxlength="2000"
                    data-field="memo"
                    data-label="{{ $columnLabels['memo'] }}"
                    @readonly(!($canEdit ?? false))
                >{{ $flowManagement->memo }}</textarea>
            </label>

            <label class="application-block__cell application-block__cell--editable">
                <span class="application-block__cell-label">{{ $columnLabels['move_in_date'] }}</span>
                <input
                    type="date"
                    class="flow-detail-field application-inline-field"
                    data-field="move_in_date"
                    data-label="{{ $columnLabels['move_in_date'] }}"
                    value="{{ $flowManagement->move_in_date?->format('Y-m-d') }}"
                    @disabled(!($canEdit ?? false))
                >
            </label>
            <label class="application-block__cell application-block__cell--editable">
                <span class="application-block__cell-label">{{ $columnLabels['document_deadline'] }}</span>
                <input
                    type="text"
                    class="flow-detail-field application-inline-field"
                    data-field="document_deadline"
                    data-label="{{ $columnLabels['document_deadline'] }}"
                    maxlength="255"
                    value="{{ $flowManagement->document_deadline }}"
                    @readonly(!($canEdit ?? false))
                >
            </label>
            <label class="application-block__cell application-block__cell--editable">
                <span class="application-block__cell-label">{{ $columnLabels['scheduled_visit_date'] }}</span>
                <input
                    type="date"
                    class="flow-detail-field application-inline-field"
                    data-field="scheduled_visit_date"
                    data-label="{{ $columnLabels['scheduled_visit_date'] }}"
                    value="{{ $flowManagement->scheduled_visit_date?->format('Y-m-d') }}"
                    @disabled(!($canEdit ?? false))
                >
            </label>
            <label class="application-block__cell application-block__cell--editable">
                <span class="application-block__cell-label">{{ $columnLabels['key_handover_date'] }}</span>
                <input
                    type="date"
                    class="flow-detail-field application-inline-field"
                    data-field="key_handover_date"
                    data-label="{{ $columnLabels['key_handover_date'] }}"
                    value="{{ $flowManagement->key_handover_date?->format('Y-m-d') }}"
                    @disabled(!($canEdit ?? false))
                >
            </label>

            @foreach ($booleanFields as $field)
                @if (in_array($field, ['settlement_transition', 'has_broker_fee'], true))
                    @continue
                @endif

                @if ($field === 'transfer_request_to_applicant')
                    <label class="application-block__cell application-block__cell--editable">
                        <span class="application-block__cell-label">{{ $columnLabels['ad_fee_invoice_creation'] }}</span>
                        <input
                            type="text"
                            class="flow-detail-field application-inline-field"
                            data-field="ad_fee_invoice_creation"
                            data-label="{{ $columnLabels['ad_fee_invoice_creation'] }}"
                            maxlength="50"
                            placeholder="済 / 不要"
                            value="{{ $flowManagement->ad_fee_invoice_creation }}"
                            @readonly(!($canEdit ?? false))
                        >
                    </label>
                @endif

                <label class="application-block__cell flow-detail-check-cell {{ $flowManagement->{$field} ? 'admin-highlight-bg' : '' }}">
                    <span class="application-block__cell-label">{{ $columnLabels[$field] }}</span>
                    <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input
                            type="checkbox"
                            class="flow-detail-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                            data-field="{{ $field }}"
                            @checked($flowManagement->{$field})
                            @disabled(!($canEdit ?? false))
                        >
                        完了
                    </span>
                </label>
            @endforeach

            <label class="application-block__cell flow-detail-check-cell {{ $flowManagement->has_broker_fee ? 'admin-highlight-bg' : '' }}">
                <span class="application-block__cell-label">{{ $columnLabels['has_broker_fee'] }}</span>
                <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input
                        type="checkbox"
                        class="flow-detail-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                        data-field="has_broker_fee"
                        @checked($flowManagement->has_broker_fee)
                        @disabled(!($canEdit ?? false))
                    >
                    あり
                </span>
            </label>
        </div>
    </div>
</div>
@endsection

@if ($canEdit ?? false)
@push('scripts')
<script>
    (() => {
        const container = document.querySelector('[data-flow-management-id]');
        if (!container) {
            return;
        }

        const id = container.dataset.flowManagementId;
        const updateUrl = adminApiUrl(`/admin/flow-managements/${id}/fields`);

        async function save(field, value) {
            const response = await fetch(updateUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ field, value }),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || '更新に失敗しました。');
            }
        }

        document.querySelectorAll('.flow-detail-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', async () => {
                const previous = !checkbox.checked;

                if (checkbox.dataset.field === 'settlement_transition' && previous) {
                    checkbox.checked = true;
                    const confirmed = await window.confirmUncheckTransition();
                    if (!confirmed) {
                        return;
                    }
                    checkbox.checked = false;
                }

                try {
                    await save(checkbox.dataset.field, checkbox.checked ? 1 : 0);
                    checkbox.closest('.flow-detail-check-cell')?.classList.toggle('admin-highlight-bg', checkbox.checked);
                } catch (error) {
                    checkbox.checked = previous;
                    alert(error.message);
                }
            });
        });

        function syncContractDocumentUrlLock(item, hasUrl, isConfirmed) {
            const urlInput = item.querySelector('.flow-detail-field');
            const urlWrap = item.querySelector('[data-contract-doc-url-wrap]');
            const lockedText = item.querySelector('[data-contract-doc-url-locked]');
            const canEdit = {{ ($canEdit ?? false) ? 'true' : 'false' }};

            if (urlInput) {
                urlInput.readOnly = !canEdit || isConfirmed;
            }

            if (urlWrap) {
                urlWrap.classList.toggle('hidden', isConfirmed);
            }

            if (lockedText) {
                lockedText.textContent = urlInput?.value.trim() || '';
                lockedText.classList.toggle('hidden', !isConfirmed || !hasUrl);
            }
        }

        function syncContractDocumentStatus(item, hasUrl, isConfirmed) {
            const statusBadge = item.querySelector('[data-contract-doc-status]');
            const confirmCheckbox = item.querySelector('.contract-doc-confirm-checkbox');
            const confirmLabel = confirmCheckbox?.closest('label');

            if (confirmCheckbox) {
                confirmCheckbox.disabled = !hasUrl;
                if (!hasUrl) {
                    confirmCheckbox.checked = false;
                    isConfirmed = false;
                }
            }
            confirmLabel?.classList.toggle('opacity-40', !hasUrl);

            syncContractDocumentUrlLock(item, hasUrl, isConfirmed);

            if (!statusBadge) {
                return;
            }

            statusBadge.classList.remove(
                'hidden',
                'bg-amber-100',
                'text-amber-900',
                'bg-emerald-100',
                'text-emerald-800',
                'bg-slate-100',
                'text-slate-500'
            );

            if (!hasUrl) {
                statusBadge.textContent = '未設定';
                statusBadge.classList.add('hidden', 'bg-slate-100', 'text-slate-500');
                return;
            }

            if (isConfirmed) {
                statusBadge.textContent = '完了';
                statusBadge.classList.add('bg-emerald-100', 'text-emerald-800');
            } else {
                statusBadge.textContent = '確認中';
                statusBadge.classList.add('bg-amber-100', 'text-amber-900');
            }
        }

        function syncContractDocumentOpenLink(field) {
            const item = field.closest('[data-contract-doc-item]');
            const openLink = item?.querySelector('[data-contract-doc-open]');
            if (!item || !openLink) {
                return;
            }

            const url = field.value.trim();
            const hasUrl = url !== '';
            openLink.href = hasUrl ? url : '#';
            openLink.classList.toggle('pointer-events-none', !hasUrl);
            openLink.classList.toggle('opacity-40', !hasUrl);
            if (hasUrl) {
                openLink.removeAttribute('aria-disabled');
            } else {
                openLink.setAttribute('aria-disabled', 'true');
            }

            const confirmCheckbox = item.querySelector('.contract-doc-confirm-checkbox');
            syncContractDocumentStatus(item, hasUrl, Boolean(confirmCheckbox?.checked));
        }

        document.querySelectorAll('.contract-doc-confirm-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', async () => {
                const item = checkbox.closest('[data-contract-doc-item]');
                const previous = !checkbox.checked;
                const urlField = item?.querySelector('.flow-detail-field');
                const hasUrl = Boolean(urlField?.value.trim());

                if (!hasUrl) {
                    checkbox.checked = false;
                    return;
                }

                try {
                    await save(checkbox.dataset.field, checkbox.checked ? 1 : 0);
                    syncContractDocumentStatus(item, true, checkbox.checked);
                } catch (error) {
                    checkbox.checked = previous;
                    alert(error.message);
                }
            });
        });

        document.querySelectorAll('.flow-detail-field').forEach((field) => {
            let previous = field.value;
            let timer = null;

            const saveField = async () => {
                if (field.readOnly || field.value === previous) {
                    return;
                }

                try {
                    await save(field.dataset.field, field.value || null);
                    previous = field.value;
                    if (field.closest('[data-contract-doc-item]')) {
                        syncContractDocumentOpenLink(field);
                    }
                } catch (error) {
                    field.value = previous;
                    alert(error.message || `${field.dataset.label}の保存に失敗しました。`);
                }
            };

            field.addEventListener('change', saveField);
            field.addEventListener('blur', saveField);
            field.addEventListener('input', () => {
                if (field.readOnly) {
                    return;
                }
                clearTimeout(timer);
                timer = setTimeout(saveField, 800);
            });
        });
    })();
</script>
@endpush
@endif
