@extends($layout)

@section('title', $showTabs ? '賃貸-'.$masterTabTitle.' マスター管理' : $pageTitle)

@push('head')
    <style>
        .master-admin-shell {
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - var(--master-header-height, var(--admin-header-height, 72px)) - 4rem);
            max-height: calc(100vh - var(--master-header-height, var(--admin-header-height, 72px)) - 4rem);
        }

        .master-page {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
        }

        .master-table-panel {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
        }

        .master-table-card {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        .master-admin-shell .admin-table-scroll {
            flex: 1 1 auto;
            min-height: 0;
            max-height: none;
            overflow: auto;
            overscroll-behavior: contain;
        }
    </style>
@endpush

@section('content')
    <div class="master-admin-shell master-page">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between shrink-0">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">{{ $pageTitle }}</h2>
        </div>
        <form method="GET" action="{{ url()->current() }}" class="flex flex-col sm:flex-row sm:items-center gap-2 w-full lg:w-auto lg:max-w-md">
            @if ($showTabs)
                <input type="hidden" name="table" value="{{ $tableKey }}">
            @endif
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="キーワードで検索"
                class="w-full rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-800 placeholder:text-slate-400 focus:border-[#5383c3] focus:outline-none focus:ring-2 focus:ring-[#5383c3]/20"
            >
            <div class="flex shrink-0 gap-1.5">
                <button type="submit" class="inline-flex items-center justify-center rounded-md bg-[#5383c3] px-3 py-1.5 text-xs font-medium text-white hover:opacity-90">
                    検索
                </button>
                @if ($search !== '')
                    <a
                        href="{{ $showTabs ? route('master.data.index', ['table' => $tableKey]) : url()->current() }}"
                        class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                    >
                        クリア
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if ($records->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center text-slate-500">
            @if ($search !== '')
                「{{ $search }}」に一致するデータがありません。
            @else
                データがありません。
            @endif
        </div>
    @else
        <div class="master-table-panel">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden master-table-card">
            <div class="admin-table-scroll">
                <table id="master-data-table" class="admin-table-sticky min-w-full text-sm text-left">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            @foreach ($columns as $column)
                                <th class="px-3 py-3 font-medium whitespace-nowrap min-w-[120px] {{ $column === 'id' ? 'sticky-col' : '' }}">
                                    {{ $columnLabels[$column] }}
                                    <div class="text-[10px] font-normal text-slate-400">{{ $column }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($records as $record)
                            <tr
                                class="align-top bg-white hover:bg-slate-50 transition-colors"
                                data-master-table="{{ $tableKey }}"
                                data-master-record-id="{{ $record->id }}"
                            >
                                @foreach ($columns as $column)
                                    @php
                                        $inputType = $columnInputTypes[$column];
                                        $fieldValue = \App\Support\MasterFieldHelper::formatValueForInput($record, $column, $inputType);
                                        $displayValue = \App\Support\MasterFieldHelper::formatValueForDisplay($record, $column);
                                    @endphp
                                    @php
                                        $tdClasses = 'px-3 py-2 min-w-[120px]';
                                        if ($column === 'id') {
                                            $tdClasses .= ' sticky-col';
                                        }
                                        if ($inputType === 'checkbox') {
                                            $tdClasses .= ' master-check-cell transition-colors';
                                            if ($record->{$column}) {
                                                $tdClasses .= ' admin-highlight-bg';
                                            }
                                        }
                                    @endphp
                                    <td class="{{ $tdClasses }}">
                                        @if (!($canEdit ?? false) || $inputType === 'readonly')
                                            <span class="text-slate-600 whitespace-nowrap">{{ $displayValue }}</span>
                                        @elseif ($inputType === 'checkbox')
                                            <div class="text-center">
                                                <input
                                                    type="checkbox"
                                                    class="master-field-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                                    data-field="{{ $column }}"
                                                    data-label="{{ $columnLabels[$column] }}"
                                                    @checked($record->{$column})
                                                >
                                            </div>
                                        @elseif ($inputType === 'select')
                                            <select
                                                class="master-field-select w-full min-w-[100px] rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 bg-white"
                                                data-field="{{ $column }}"
                                                data-label="{{ $columnLabels[$column] }}"
                                            >
                                                @foreach (\App\Support\MasterFieldHelper::selectOptions($column) as $optionValue => $optionLabel)
                                                    <option value="{{ $optionValue }}" @selected((string) $fieldValue === (string) $optionValue)>{{ $optionLabel }}</option>
                                                @endforeach
                                            </select>
                                        @elseif ($inputType === 'textarea')
                                            <textarea
                                                class="master-field-text w-full min-w-[140px] min-h-[2.5rem] rounded border border-slate-200 px-2 py-1 text-sm resize-y focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                                data-field="{{ $column }}"
                                                data-label="{{ $columnLabels[$column] }}"
                                                rows="2"
                                            >{{ $fieldValue }}</textarea>
                                        @elseif ($inputType === 'password')
                                            <input
                                                type="password"
                                                class="master-field-text w-full min-w-[120px] rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                                data-field="{{ $column }}"
                                                data-label="{{ $columnLabels[$column] }}"
                                                placeholder="変更時のみ入力"
                                                autocomplete="new-password"
                                            >
                                        @else
                                            <input
                                                type="{{ $inputType === 'number' ? 'number' : ($inputType === 'date' ? 'date' : ($inputType === 'datetime' ? 'datetime-local' : 'text')) }}"
                                                class="master-field-text w-full min-w-[120px] rounded border border-slate-200 px-2 py-1 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                                data-field="{{ $column }}"
                                                data-label="{{ $columnLabels[$column] }}"
                                                @if ($inputType === 'number') min="0" step="1" @endif
                                                value="{{ $fieldValue }}"
                                            >
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($records->hasPages())
            <div class="mt-4 pb-1 shrink-0">
                {{ $records->links('vendor.pagination.admin') }}
            </div>
        @endif
        </div>
    @endif
    </div>
@endsection

@if ($canEdit ?? false)
@push('scripts')
<script>
    function masterFieldUpdateUrl(table, recordId) {
        return adminApiUrl('/master/data/' + encodeURIComponent(table) + '/' + encodeURIComponent(recordId) + '/fields');
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    }

    function updateMasterCheckCell(checkbox) {
        const cell = checkbox.closest('td.master-check-cell');
        if (!cell) return;
        cell.classList.toggle('admin-highlight-bg', checkbox.checked);

        const table = cell.closest('table');
        if (table && typeof window.refreshAdminStickyColumns === 'function') {
            window.refreshAdminStickyColumns();
        }
    }

    async function saveMasterField(table, recordId, field, value, fieldLabel) {
        const response = await fetch(masterFieldUpdateUrl(table, recordId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ field, value }),
        });

        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            const validationMessage = data.errors?.value?.[0];
            throw new Error(validationMessage || data.message || `${fieldLabel}の保存に失敗しました。`);
        }

        return response.json();
    }

    function masterRowFromTarget(target) {
        return target.closest('tr[data-master-record-id]');
    }

    const masterTable = document.getElementById('master-data-table');
    const masterFieldPreviousValues = new WeakMap();
    const masterSaveTimers = new WeakMap();

    if (masterTable) {
        masterTable.querySelectorAll('.master-field-checkbox, .master-field-select, .master-field-text').forEach((field) => {
            masterFieldPreviousValues.set(field, field.type === 'checkbox' ? field.checked : field.value);
        });

        masterTable.querySelectorAll('.master-field-checkbox').forEach((checkbox) => {
            updateMasterCheckCell(checkbox);
        });

        masterTable.addEventListener('change', async (event) => {
            const target = event.target;

            if (target.classList.contains('master-field-checkbox')) {
                const row = masterRowFromTarget(target);
                if (!row) return;

                const previousChecked = !target.checked;
                const value = target.checked ? 1 : 0;

                updateMasterCheckCell(target);

                try {
                    await saveMasterField(
                        row.dataset.masterTable,
                        row.dataset.masterRecordId,
                        target.dataset.field,
                        value,
                        target.dataset.label
                    );
                    masterFieldPreviousValues.set(target, target.checked);
                } catch (error) {
                    target.checked = previousChecked;
                    updateMasterCheckCell(target);
                    alert(error.message || '更新に失敗しました。');
                }

                return;
            }

            if (target.classList.contains('master-field-select')) {
                const row = masterRowFromTarget(target);
                if (!row) return;

                const previousValue = masterFieldPreviousValues.get(target) ?? target.value;
                const value = target.value === '' ? null : target.value;

                if (target.value === previousValue) return;

                try {
                    await saveMasterField(
                        row.dataset.masterTable,
                        row.dataset.masterRecordId,
                        target.dataset.field,
                        value,
                        target.dataset.label
                    );
                    masterFieldPreviousValues.set(target, target.value);
                } catch (error) {
                    target.value = previousValue;
                    alert(error.message);
                }

                return;
            }

            if (target.classList.contains('master-field-text') && (target.type === 'date' || target.type === 'datetime-local')) {
                const row = masterRowFromTarget(target);
                if (!row) return;

                const previousValue = masterFieldPreviousValues.get(target) ?? target.value;
                const value = target.value || null;

                if (value === previousValue) return;

                try {
                    await saveMasterField(
                        row.dataset.masterTable,
                        row.dataset.masterRecordId,
                        target.dataset.field,
                        value,
                        target.dataset.label
                    );
                    masterFieldPreviousValues.set(target, target.value);
                } catch (error) {
                    target.value = previousValue;
                    alert(error.message);
                }
            }
        });

        masterTable.addEventListener('blur', async (event) => {
            const target = event.target;
            if (!target.classList.contains('master-field-text') || target.type === 'date' || target.type === 'datetime-local') {
                return;
            }

            const row = masterRowFromTarget(target);
            if (!row) return;

            const fieldName = target.dataset.field;
            const fieldLabel = target.dataset.label || fieldName;
            const isPassword = target.type === 'password';
            let value = target.value;

            if (isPassword && value === '') {
                return;
            }

            if (target.type === 'number') {
                value = value === '' ? null : Number(value);
            } else if (value === '') {
                value = null;
            }

            const previousValue = masterFieldPreviousValues.get(target) ?? target.value;
            if (String(target.value) === String(previousValue)) {
                return;
            }

            try {
                const result = await saveMasterField(
                    row.dataset.masterTable,
                    row.dataset.masterRecordId,
                    fieldName,
                    value,
                    fieldLabel
                );

                if (isPassword) {
                    target.value = '';
                    masterFieldPreviousValues.set(target, '');
                } else if (result.input !== undefined) {
                    target.value = result.input;
                    masterFieldPreviousValues.set(target, result.input);
                } else {
                    masterFieldPreviousValues.set(target, target.value);
                }
            } catch (error) {
                target.value = previousValue;
                alert(error.message);
            }
        }, true);

        masterTable.addEventListener('input', (event) => {
            const target = event.target;
            if (!target.classList.contains('master-field-text') || target.tagName !== 'TEXTAREA') {
                return;
            }

            clearTimeout(masterSaveTimers.get(target));

            const timer = setTimeout(async () => {
                const row = masterRowFromTarget(target);
                if (!row) return;

                const fieldName = target.dataset.field;
                const fieldLabel = target.dataset.label || fieldName;
                const value = target.value;
                const previousValue = masterFieldPreviousValues.get(target) ?? target.value;

                if (value === previousValue) {
                    return;
                }

                try {
                    await saveMasterField(
                        row.dataset.masterTable,
                        row.dataset.masterRecordId,
                        fieldName,
                        value,
                        fieldLabel
                    );
                    masterFieldPreviousValues.set(target, value);
                } catch (error) {
                    target.value = previousValue;
                    alert(error.message);
                }
            }, 800);

            masterSaveTimers.set(target, timer);
        });
    }
</script>
@endpush
@endif
