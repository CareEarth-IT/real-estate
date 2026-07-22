@extends('layouts.admin')

@section('title', '決済金管理詳細 — ' . config('app.name'))

@section('content')
<div data-settlement-management-id="{{ $settlementManagement->id }}">
    <div class="mb-6">
        <a href="{{ route('admin.settlement-managements.index') }}" class="mb-4 inline-flex text-sm text-primary-600 hover:underline">
            ← 決済金管理へ戻る
        </a>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">決済金管理詳細</h2>
                <p class="mt-2 text-sm text-slate-600">
                    <strong class="text-slate-900">{{ $settlementManagement->property_name ?: '（物件名未設定）' }}</strong>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($settlementManagement->feeTypeBadges() as $badge)
                    <span class="inline-flex w-fit items-center rounded-md border px-3 py-1.5 text-sm font-semibold {{ $badge['classes'] }}">
                        {{ $badge['label'] }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="application-blocks-board">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
            <div class="application-block__cell">
                <span class="application-block__cell-label">作成日時</span>
                <div class="application-block__cell-value">{{ $settlementManagement->created_at?->format('Y/m/d H:i') ?? '—' }}</div>
            </div>
            <div class="application-block__cell">
                <span class="application-block__cell-label">更新日時</span>
                <div class="application-block__cell-value">{{ $settlementManagement->updated_at?->format('Y/m/d H:i') ?? '—' }}</div>
            </div>
            <div class="application-block__cell">
                <span class="application-block__cell-label">{{ $columnLabels['fee_type'] }}</span>
                <div class="application-block__cell-value">{{ $settlementManagement->feeTypeLabel() ?: '—' }}</div>
            </div>
            <div class="application-block__cell">
                <span class="application-block__cell-label">{{ $columnLabels['staff_in_charge'] }}</span>
                <div class="application-block__cell-value">{{ $settlementManagement->staff_in_charge ?: '—' }}</div>
            </div>
            <div class="application-block__cell">
                <span class="application-block__cell-label">{{ $columnLabels['property_name'] }}</span>
                <div class="application-block__cell-value">{{ $settlementManagement->property_name ?: '—' }}</div>
            </div>

            <label class="application-block__cell application-block__cell--editable">
                <span class="application-block__cell-label">{{ $columnLabels['management_number'] }}</span>
                <input type="text" class="settlement-detail-field application-inline-field"
                    data-field="management_number" data-label="{{ $columnLabels['management_number'] }}"
                    maxlength="255" value="{{ $settlementManagement->management_number }}"
                    @readonly(!($canEdit ?? false))>
            </label>
            <label class="application-block__cell application-block__cell--editable">
                <span class="application-block__cell-label">{{ $columnLabels['contract_date'] }}</span>
                <input type="date" class="settlement-detail-field application-inline-field"
                    data-field="contract_date" data-label="{{ $columnLabels['contract_date'] }}"
                    value="{{ $settlementManagement->contract_date?->format('Y-m-d') }}"
                    @disabled(!($canEdit ?? false))>
            </label>
            <label class="application-block__cell application-block__cell--editable">
                <span class="application-block__cell-label">{{ $columnLabels['settlement_transfer_date'] }}</span>
                <input type="date" class="settlement-detail-field application-inline-field"
                    data-field="settlement_transfer_date" data-label="{{ $columnLabels['settlement_transfer_date'] }}"
                    value="{{ $settlementManagement->settlement_transfer_date?->format('Y-m-d') }}"
                    @disabled(!($canEdit ?? false))>
            </label>

            @if ($settlementManagement->hasAdvertisingFee())
                <label class="application-block__cell application-block__cell--editable">
                    <span class="application-block__cell-label">{{ $columnLabels['advertising_fee_amount'] }}</span>
                    <input type="number" min="0" class="settlement-detail-field application-inline-field"
                        data-field="advertising_fee_amount" data-label="{{ $columnLabels['advertising_fee_amount'] }}" data-value-type="integer"
                        value="{{ $settlementManagement->advertising_fee_amount }}"
                        @readonly(!($canEdit ?? false))>
                </label>
            @endif

            @if ($settlementManagement->hasBrokerFee())
                <label class="application-block__cell application-block__cell--editable">
                    <span class="application-block__cell-label">{{ $columnLabels['broker_fee_amount'] }}</span>
                    <input type="number" min="0" class="settlement-detail-field application-inline-field"
                        data-field="broker_fee_amount" data-label="{{ $columnLabels['broker_fee_amount'] }}" data-value-type="integer"
                        value="{{ $settlementManagement->broker_fee_amount }}"
                        @readonly(!($canEdit ?? false))>
                </label>
            @endif

            <label class="application-block__cell application-block__cell--editable">
                <span class="application-block__cell-label">{{ $columnLabels['estimated_sales'] }}</span>
                <input type="number" min="0" class="settlement-detail-field application-inline-field"
                    data-field="estimated_sales" data-label="{{ $columnLabels['estimated_sales'] }}" data-value-type="integer"
                    value="{{ $settlementManagement->estimated_sales }}"
                    @readonly(!($canEdit ?? false))>
            </label>

            @foreach (['sales_including_tax', 'sales_excluding_tax'] as $field)
                <label class="application-block__cell application-block__cell--editable">
                    <span class="application-block__cell-label">{{ $columnLabels[$field] }}</span>
                    <input type="number" min="0" class="settlement-detail-field application-inline-field"
                        data-field="{{ $field }}" data-label="{{ $columnLabels[$field] }}" data-value-type="integer"
                        value="{{ $settlementManagement->{$field} }}"
                        @readonly(!($canEdit ?? false))>
                </label>
            @endforeach

            <label class="application-block__cell application-block__cell--editable">
                <span class="application-block__cell-label">{{ $columnLabels['earned_points'] }}</span>
                <input type="text" class="settlement-detail-field application-inline-field"
                    data-field="earned_points" data-label="{{ $columnLabels['earned_points'] }}"
                    maxlength="255" value="{{ $settlementManagement->earned_points }}"
                    @readonly(!($canEdit ?? false))>
            </label>

            @foreach ($booleanFields as $field)
                <label class="application-block__cell settlement-detail-check-cell {{ $settlementManagement->{$field} ? 'admin-highlight-bg' : '' }}">
                    <span class="application-block__cell-label">{{ $columnLabels[$field] }}</span>
                    <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input type="checkbox"
                            class="settlement-detail-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                            data-field="{{ $field }}"
                            @checked($settlementManagement->{$field})
                            @disabled(!($canEdit ?? false))>
                        完了
                    </span>
                </label>
            @endforeach

            <label class="application-block__cell application-block__cell--editable md:col-span-2 xl:col-span-3">
                <span class="application-block__cell-label">{{ $columnLabels['remarks'] }}</span>
                <textarea class="settlement-detail-field application-inline-field" rows="4" maxlength="2000"
                    data-field="remarks" data-label="{{ $columnLabels['remarks'] }}"
                    @readonly(!($canEdit ?? false))>{{ $settlementManagement->remarks }}</textarea>
            </label>
        </div>
    </div>
</div>
@endsection

@if ($canEdit ?? false)
@push('scripts')
<script>
    (() => {
        const container = document.querySelector('[data-settlement-management-id]');
        if (!container) {
            return;
        }

        const updateUrl = adminApiUrl(`/admin/settlement-managements/${container.dataset.settlementManagementId}/fields`);

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
                const validationMessage = data.errors?.value?.[0] ?? data.errors?.field?.[0];
                throw new Error(validationMessage || data.message || '更新に失敗しました。');
            }
        }

        document.querySelectorAll('.settlement-detail-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', async () => {
                const previous = !checkbox.checked;
                const cell = checkbox.closest('.settlement-detail-check-cell');
                cell?.classList.toggle('admin-highlight-bg', checkbox.checked);

                try {
                    await save(checkbox.dataset.field, checkbox.checked ? 1 : 0);
                } catch (error) {
                    checkbox.checked = previous;
                    cell?.classList.toggle('admin-highlight-bg', checkbox.checked);
                    alert(error.message);
                }
            });
        });

        document.querySelectorAll('.settlement-detail-field').forEach((field) => {
            let previous = field.value;
            let timer = null;

            const saveField = async () => {
                if (field.value === previous) {
                    return;
                }

                const value = field.dataset.valueType === 'integer'
                    ? (field.value === '' ? null : Number(field.value))
                    : (field.value || null);

                try {
                    await save(field.dataset.field, value);
                    previous = field.value;
                } catch (error) {
                    field.value = previous;
                    alert(error.message || `${field.dataset.label}の保存に失敗しました。`);
                }
            };

            field.addEventListener('change', saveField);
            field.addEventListener('blur', saveField);
            field.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(saveField, 800);
            });
        });
    })();
</script>
@endpush
@endif
