@extends('layouts.admin')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-2xl font-bold text-slate-900">書類管理</h2>
        <x-admin-search-form :value="$search" />
    </div>

    @if ($flowManagements->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center text-slate-500">
            @if ($search !== '')
                「{{ $search }}」に一致するデータがありません。
            @else
                書類管理のデータがありません。
            @endif
        </div>
    @else
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="admin-table-scroll overflow-x-auto">
                <table class="admin-table-sticky min-w-full text-sm text-left" data-sticky-cols="4">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="sticky-col px-3 py-3 font-medium whitespace-nowrap min-w-[130px]">作成日時</th>
                            <th class="sticky-col px-3 py-3 font-medium whitespace-nowrap min-w-[90px]">{{ $columnLabels['staff_in_charge'] }}</th>
                            <th class="sticky-col px-3 py-3 font-medium whitespace-nowrap min-w-[140px]">{{ $columnLabels['property_name'] }}</th>
                            <th class="sticky-col sticky-col-last px-3 py-3 font-medium whitespace-nowrap min-w-[90px]">{{ $columnLabels['room_number'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">{{ $columnLabels['application_method'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[180px]">{{ $columnLabels['memo'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">{{ $columnLabels['move_in_date'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">{{ $columnLabels['document_deadline'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">{{ $columnLabels['scheduled_visit_date'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">{{ $columnLabels['key_handover_date'] }}</th>
                            @foreach ($booleanFields as $field)
                                @if (in_array($field, ['settlement_transition', 'has_broker_fee'], true))
                                    @continue
                                @endif
                                @if ($field === 'transfer_request_to_applicant')
                                    <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[110px]">
                                        <div>{{ $columnLabels['ad_fee_invoice_creation'] }}</div>
                                        <div class="text-xs font-normal text-slate-500 mt-0.5">済か不要と入力</div>
                                    </th>
                                @endif
                                <th class="px-3 py-3 font-medium whitespace-nowrap text-center">{{ $columnLabels[$field] }}</th>
                            @endforeach
                            <th class="px-3 py-3 font-medium whitespace-nowrap text-center">{{ $columnLabels['has_broker_fee'] }}</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap text-center">{{ $columnLabels['settlement_transition'] }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($flowManagements as $flowManagement)
                            <tr
                                class="align-top bg-white hover:bg-slate-50 transition-colors {{ $flowManagement->settlement_transition ? 'has-sticky-highlight-blue' : '' }}"
                                data-flow-management-id="{{ $flowManagement->id }}"
                            >
                                <td class="sticky-col px-3 py-3 whitespace-nowrap">{{ $flowManagement->application?->created_at?->format('Y/m/d H:i') ?? '—' }}</td>
                                <td class="sticky-col px-3 py-3 whitespace-nowrap">{{ $flowManagement->staff_in_charge ?? '—' }}</td>
                                <td class="sticky-col px-3 py-3 whitespace-nowrap">{{ $flowManagement->property_name ?? '—' }}</td>
                                <td class="sticky-col sticky-col-last px-3 py-3 whitespace-nowrap">{{ $flowManagement->room_number ?? '—' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $flowManagement->application_method ?? '—' }}</td>
                                <td class="px-3 py-3 min-w-[180px]">
                                    <textarea
                                        class="flow-memo-field w-full min-h-[2.5rem] rounded border border-slate-200 px-2 py-1 text-sm resize-y focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        rows="2"
                                        maxlength="2000"
                                        placeholder="備考を入力"
                                        @readonly(!($canEdit ?? false))
                                    >{{ $flowManagement->memo }}</textarea>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <input
                                        type="date"
                                        class="flow-date-field rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        data-field="move_in_date"
                                        data-label="入居日"
                                        value="{{ $flowManagement->move_in_date?->format('Y-m-d') }}"
                                        @disabled(!($canEdit ?? false))
                                    >
                                </td>
                                <td class="px-3 py-3 min-w-[120px]">
                                    <input
                                        type="text"
                                        class="flow-inline-text-field w-full rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        data-field="document_deadline"
                                        data-label="書類期日"
                                        maxlength="255"
                                        value="{{ $flowManagement->document_deadline }}"
                                        @readonly(!($canEdit ?? false))
                                    >
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <input
                                        type="date"
                                        class="flow-date-field rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        data-field="scheduled_visit_date"
                                        data-label="来社予定日"
                                        value="{{ $flowManagement->scheduled_visit_date?->format('Y-m-d') }}"
                                        @disabled(!($canEdit ?? false))
                                    >
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <input
                                        type="date"
                                        class="flow-date-field rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        data-field="key_handover_date"
                                        data-label="鍵渡日"
                                        value="{{ $flowManagement->key_handover_date?->format('Y-m-d') }}"
                                        @disabled(!($canEdit ?? false))
                                    >
                                </td>
                                @foreach ($booleanFields as $field)
                                    @if (in_array($field, ['settlement_transition', 'has_broker_fee'], true))
                                        @continue
                                    @endif
                                    @if ($field === 'transfer_request_to_applicant')
                                        <td class="px-3 py-3 min-w-[110px]">
                                            <input
                                                type="text"
                                                class="flow-inline-text-field w-full rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                                data-field="ad_fee_invoice_creation"
                                                data-label="広告料請求書作成"
                                                maxlength="50"
                                                placeholder="済 / 不要"
                                                value="{{ $flowManagement->ad_fee_invoice_creation }}"
                                                @readonly(!($canEdit ?? false))
                                            >
                                        </td>
                                    @endif
                                    <td class="px-3 py-3 text-center flow-check-cell transition-colors {{ $flowManagement->{$field} ? 'admin-highlight-bg' : '' }}">
                                        <input
                                            type="checkbox"
                                            class="flow-field-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                            data-field="{{ $field }}"
                                            @checked($flowManagement->{$field})
                                            @disabled(!($canEdit ?? false))
                                        >
                                    </td>
                                @endforeach
                                <td class="px-3 py-3 text-center flow-check-cell transition-colors {{ $flowManagement->has_broker_fee ? 'admin-highlight-bg' : '' }}">
                                    <input
                                        type="checkbox"
                                        class="flow-field-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        data-field="has_broker_fee"
                                        @checked($flowManagement->has_broker_fee)
                                        @disabled(!($canEdit ?? false))
                                    >
                                </td>
                                <td class="px-3 py-3 text-center flow-check-cell transition-colors {{ $flowManagement->settlement_transition ? 'admin-highlight-bg' : '' }}">
                                    <input
                                        type="checkbox"
                                        class="flow-field-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        data-field="settlement_transition"
                                        @checked($flowManagement->settlement_transition)
                                        @disabled(!($canEdit ?? false))
                                    >
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($flowManagements->hasPages())
            <div class="mt-6 pb-2">
                {{ $flowManagements->links('vendor.pagination.admin') }}
            </div>
        @endif
    @endif
@endsection

@if ($canEdit ?? false)
@push('scripts')
<script>
    function updateCellState(checkbox) {
        const cell = checkbox.closest('.flow-check-cell');
        if (!cell) {
            return;
        }

        cell.classList.toggle('admin-highlight-bg', checkbox.checked);
    }

    function updateStickyColState(row) {
        const settlementCheckbox = row.querySelector('.flow-field-checkbox[data-field="settlement_transition"]');
        if (!settlementCheckbox) {
            return;
        }

        row.classList.toggle('has-sticky-highlight-blue', settlementCheckbox.checked);

        if (typeof window.refreshAdminStickyColumns === 'function') {
            window.refreshAdminStickyColumns();
        }
    }

    document.querySelectorAll('tbody tr').forEach((row) => {
        updateStickyColState(row);
    });

    document.querySelectorAll('.flow-field-checkbox').forEach((checkbox) => {
        updateCellState(checkbox);

        checkbox.addEventListener('change', async (event) => {
            const target = event.target;
            const row = target.closest('tr');
            const flowManagementId = row.dataset.flowManagementId;
            const field = target.dataset.field;
            const previousChecked = !target.checked;
            const value = target.checked ? 1 : 0;

            if (field === 'settlement_transition' && previousChecked) {
                target.checked = true;
                updateCellState(target);
                updateStickyColState(row);

                const confirmed = await window.confirmUncheckTransition();
                if (!confirmed) {
                    return;
                }

                target.checked = false;
            }

            updateCellState(target);
            if (field === 'settlement_transition') {
                updateStickyColState(row);
            }

            try {
                const response = await fetch(adminApiUrl(`/admin/flow-managements/${flowManagementId}/fields`), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ field, value }),
                });

                const data = await response.json();

                if (!response.ok) {
                    target.checked = previousChecked;
                    updateCellState(target);
                    if (field === 'settlement_transition') {
                        updateStickyColState(row);
                    }
                    alert(data.message || '更新に失敗しました。もう一度お試しください。');
                }
            } catch (error) {
                target.checked = previousChecked;
                updateCellState(target);
                if (field === 'settlement_transition') {
                    updateStickyColState(row);
                }
                alert('更新に失敗しました。もう一度お試しください。');
            }
        });
    });

    document.querySelectorAll('.flow-inline-text-field').forEach((field) => {
        let previousValue = field.value;
        let saveTimer = null;
        const fieldName = field.dataset.field;
        const fieldLabel = field.dataset.label || fieldName;

        const saveField = async () => {
            const row = field.closest('tr');
            const flowManagementId = row.dataset.flowManagementId;
            const value = field.value;

            if (value === previousValue) {
                return;
            }

            try {
                const response = await fetch(adminApiUrl(`/admin/flow-managements/${flowManagementId}/fields`), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ field: fieldName, value }),
                });

                if (!response.ok) {
                    field.value = previousValue;
                    const data = await response.json();
                    alert(data.message || `${fieldLabel}の保存に失敗しました。`);
                    return;
                }

                previousValue = value;
            } catch (error) {
                field.value = previousValue;
                alert(`${fieldLabel}の保存に失敗しました。`);
            }
        };

        field.addEventListener('blur', saveField);

        field.addEventListener('input', () => {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveField, 800);
        });
    });

    document.querySelectorAll('.flow-date-field').forEach((field) => {
        let previousValue = field.value;
        const fieldName = field.dataset.field;
        const fieldLabel = field.dataset.label || fieldName;

        const saveField = async () => {
            const row = field.closest('tr');
            const flowManagementId = row.dataset.flowManagementId;
            const value = field.value;

            if (value === previousValue) {
                return;
            }

            try {
                const response = await fetch(adminApiUrl(`/admin/flow-managements/${flowManagementId}/fields`), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ field: fieldName, value: value || null }),
                });

                if (!response.ok) {
                    field.value = previousValue;
                    const data = await response.json();
                    alert(data.message || `${fieldLabel}の保存に失敗しました。`);
                    return;
                }

                previousValue = value;
            } catch (error) {
                field.value = previousValue;
                alert(`${fieldLabel}の保存に失敗しました。`);
            }
        };

        field.addEventListener('change', saveField);
    });

    document.querySelectorAll('.flow-memo-field').forEach((textarea) => {
        let previousValue = textarea.value;
        let saveTimer = null;

        const saveMemo = async () => {
            const row = textarea.closest('tr');
            const flowManagementId = row.dataset.flowManagementId;
            const value = textarea.value;

            if (value === previousValue) {
                return;
            }

            try {
                const response = await fetch(adminApiUrl(`/admin/flow-managements/${flowManagementId}/fields`), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ field: 'memo', value }),
                });

                if (!response.ok) {
                    textarea.value = previousValue;
                    const data = await response.json();
                    alert(data.message || '備考の保存に失敗しました。');
                    return;
                }

                previousValue = value;
            } catch (error) {
                textarea.value = previousValue;
                alert('備考の保存に失敗しました。');
            }
        };

        textarea.addEventListener('blur', saveMemo);

        textarea.addEventListener('input', () => {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveMemo, 800);
        });
    });
</script>
@endpush
@endif
