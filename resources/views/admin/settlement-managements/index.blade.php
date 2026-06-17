@extends('layouts.admin')

@section('title', '決済金管理 - 管理画面')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">決済金管理</h2>
    </div>

    @if ($settlementManagements->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center text-slate-500">
            決済金管理に移行チェック済みのデータがありません。
        </div>
    @else
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table-sticky min-w-full text-sm text-left" data-sticky-cols="4">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="sticky-col px-3 py-3 font-medium whitespace-nowrap min-w-[130px]">作成日時</th>
                            <th class="sticky-col px-3 py-3 font-medium whitespace-nowrap min-w-[90px]">{{ $columnLabels['staff_in_charge'] }}</th>
                            <th class="sticky-col px-3 py-3 font-medium whitespace-nowrap min-w-[160px]">{{ $columnLabels['property_name'] }}</th>
                            <th class="sticky-col sticky-col-last px-3 py-3 font-medium whitespace-nowrap min-w-[110px]">{{ $columnLabels['contract_date'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[100px]">{{ $columnLabels['estimated_sales'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap text-center">{{ $columnLabels['settlement_transfer_request'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">{{ $columnLabels['settlement_transfer_date'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[100px]">{{ $columnLabels['sales_including_tax'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[100px]">{{ $columnLabels['sales_excluding_tax'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[100px]">{{ $columnLabels['earned_points'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap text-center">{{ $columnLabels['ad_transfer_invoice_creation'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap text-center">{{ $columnLabels['offset_statement_printing'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap text-center">{{ $columnLabels['individual_invoice_printing'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[180px]">{{ $columnLabels['remarks'] }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($settlementManagements as $settlementManagement)
                            <tr
                                class="align-top bg-white hover:bg-slate-50 transition-colors"
                                data-settlement-management-id="{{ $settlementManagement->id }}"
                            >
                                <td class="sticky-col px-3 py-3 whitespace-nowrap">
                                    {{ $settlementManagement->flowManagement?->screeningCompletion?->application?->created_at?->format('Y/m/d H:i') ?? '—' }}
                                </td>
                                <td class="sticky-col px-3 py-3 whitespace-nowrap">{{ $settlementManagement->staff_in_charge ?? '—' }}</td>
                                <td class="sticky-col px-3 py-3 whitespace-nowrap">{{ $settlementManagement->property_name ?? '—' }}</td>
                                <td class="sticky-col sticky-col-last px-3 py-3 whitespace-nowrap">
                                    {{ $settlementManagement->customer?->contract_period?->format('Y/m/d') ?? '—' }}
                                </td>
                                <td class="px-3 py-3 min-w-[100px]">
                                    <input
                                        type="number"
                                        min="0"
                                        class="settlement-integer-field w-full rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        data-field="estimated_sales"
                                        data-label="想定売上"
                                        value="{{ $settlementManagement->estimated_sales }}"
                                    >
                                </td>
                                <td class="px-3 py-3 text-center settlement-check-cell transition-colors {{ $settlementManagement->settlement_transfer_request ? 'bg-blue-100 text-blue-900' : '' }}">
                                    <input
                                        type="checkbox"
                                        class="settlement-field-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        data-field="settlement_transfer_request"
                                        @checked($settlementManagement->settlement_transfer_request)
                                    >
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <input
                                        type="date"
                                        class="settlement-date-field rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        data-field="settlement_transfer_date"
                                        data-label="決済金振込日"
                                        value="{{ $settlementManagement->settlement_transfer_date?->format('Y-m-d') }}"
                                    >
                                </td>
                                <td class="px-3 py-3 min-w-[100px]">
                                    <input
                                        type="number"
                                        min="0"
                                        class="settlement-integer-field w-full rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        data-field="sales_including_tax"
                                        data-label="税込売上"
                                        value="{{ $settlementManagement->sales_including_tax }}"
                                    >
                                </td>
                                <td class="px-3 py-3 min-w-[100px]">
                                    <input
                                        type="number"
                                        min="0"
                                        class="settlement-integer-field w-full rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        data-field="sales_excluding_tax"
                                        data-label="税抜売上"
                                        value="{{ $settlementManagement->sales_excluding_tax }}"
                                    >
                                </td>
                                <td class="px-3 py-3 min-w-[100px]">
                                    <input
                                        type="text"
                                        class="settlement-inline-text-field w-full rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        data-field="earned_points"
                                        data-label="発生ポイント"
                                        maxlength="255"
                                        value="{{ $settlementManagement->earned_points }}"
                                    >
                                </td>
                                @foreach (['ad_transfer_invoice_creation', 'offset_statement_printing', 'individual_invoice_printing'] as $field)
                                    <td class="px-3 py-3 text-center settlement-check-cell transition-colors {{ $settlementManagement->{$field} ? 'bg-blue-100 text-blue-900' : '' }}">
                                        <input
                                            type="checkbox"
                                            class="settlement-field-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                            data-field="{{ $field }}"
                                            @checked($settlementManagement->{$field})
                                        >
                                    </td>
                                @endforeach
                                <td class="px-3 py-3 min-w-[180px]">
                                    <textarea
                                        class="settlement-remarks-field w-full min-h-[2.5rem] rounded border border-slate-200 px-2 py-1 text-sm resize-y focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        rows="2"
                                        maxlength="2000"
                                        placeholder="備考を入力"
                                    >{{ $settlementManagement->remarks }}</textarea>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    function updateSettlementCellState(checkbox) {
        const cell = checkbox.closest('.settlement-check-cell');
        if (!cell) {
            return;
        }

        cell.classList.toggle('bg-blue-100', checkbox.checked);
        cell.classList.toggle('text-blue-900', checkbox.checked);
    }

    async function saveSettlementField(settlementManagementId, field, value, fieldLabel) {
        const response = await fetch(`/admin/settlement-managements/${settlementManagementId}/fields`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ field, value }),
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || `${fieldLabel}の保存に失敗しました。`);
        }
    }

    document.querySelectorAll('.settlement-field-checkbox').forEach((checkbox) => {
        updateSettlementCellState(checkbox);

        checkbox.addEventListener('change', async (event) => {
            const target = event.target;
            const row = target.closest('tr');
            const settlementManagementId = row.dataset.settlementManagementId;
            const field = target.dataset.field;
            const previousChecked = !target.checked;
            const value = target.checked ? 1 : 0;

            updateSettlementCellState(target);

            try {
                await saveSettlementField(settlementManagementId, field, value, field);
            } catch (error) {
                target.checked = previousChecked;
                updateSettlementCellState(target);
                alert(error.message || '更新に失敗しました。もう一度お試しください。');
            }
        });
    });

    document.querySelectorAll('.settlement-inline-text-field').forEach((field) => {
        let previousValue = field.value;
        let saveTimer = null;
        const fieldName = field.dataset.field;
        const fieldLabel = field.dataset.label || fieldName;

        const saveField = async () => {
            const row = field.closest('tr');
            const settlementManagementId = row.dataset.settlementManagementId;
            const value = field.value;

            if (value === previousValue) {
                return;
            }

            try {
                await saveSettlementField(settlementManagementId, fieldName, value, fieldLabel);
                previousValue = value;
            } catch (error) {
                field.value = previousValue;
                alert(error.message);
            }
        };

        field.addEventListener('blur', saveField);
        field.addEventListener('input', () => {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveField, 800);
        });
    });

    document.querySelectorAll('.settlement-integer-field').forEach((field) => {
        let previousValue = field.value;
        const fieldName = field.dataset.field;
        const fieldLabel = field.dataset.label || fieldName;

        const saveField = async () => {
            const row = field.closest('tr');
            const settlementManagementId = row.dataset.settlementManagementId;
            const value = field.value === '' ? null : Number(field.value);

            if (field.value === previousValue) {
                return;
            }

            try {
                await saveSettlementField(settlementManagementId, fieldName, value, fieldLabel);
                previousValue = field.value;
            } catch (error) {
                field.value = previousValue;
                alert(error.message);
            }
        };

        field.addEventListener('blur', saveField);
        field.addEventListener('change', saveField);
    });

    document.querySelectorAll('.settlement-date-field').forEach((field) => {
        let previousValue = field.value;
        const fieldName = field.dataset.field;
        const fieldLabel = field.dataset.label || fieldName;

        field.addEventListener('change', async () => {
            const row = field.closest('tr');
            const settlementManagementId = row.dataset.settlementManagementId;
            const value = field.value || null;

            if (value === previousValue) {
                return;
            }

            try {
                await saveSettlementField(settlementManagementId, fieldName, value, fieldLabel);
                previousValue = field.value;
            } catch (error) {
                field.value = previousValue;
                alert(error.message);
            }
        });
    });

    document.querySelectorAll('.settlement-remarks-field').forEach((textarea) => {
        let previousValue = textarea.value;
        let saveTimer = null;

        const saveField = async () => {
            const row = textarea.closest('tr');
            const settlementManagementId = row.dataset.settlementManagementId;
            const value = textarea.value;

            if (value === previousValue) {
                return;
            }

            try {
                await saveSettlementField(settlementManagementId, 'remarks', value, '備考');
                previousValue = value;
            } catch (error) {
                textarea.value = previousValue;
                alert(error.message);
            }
        };

        textarea.addEventListener('blur', saveField);
        textarea.addEventListener('input', () => {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveField, 800);
        });
    });
</script>
@endpush
