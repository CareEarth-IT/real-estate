@extends('layouts.admin')

@section('title', '審査完了一覧 - 管理画面')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-2xl font-bold text-slate-900">審査完了一覧</h2>
        <x-admin-search-form :value="$search" />
    </div>

    @if ($screeningCompletions->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center text-slate-500">
            @if ($search !== '')
                「{{ $search }}」に一致するデータがありません。
            @else
                審査ＯＫの申込データがありません。
            @endif
        </div>
    @else
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="resizable-table admin-table-sticky min-w-full text-sm text-left" data-table-id="screening-completions" data-sticky-cols="3">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="sticky-col px-3 py-3 font-medium whitespace-nowrap min-w-[130px]">作成日時</th>
                            <th class="sticky-col px-3 py-3 font-medium whitespace-nowrap min-w-[90px]">担当者</th>
                            <th class="sticky-col sticky-col-last px-3 py-3 font-medium whitespace-nowrap min-w-[160px]">物件名＋部屋番号</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">申込方法</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap text-center">フロー管理移行チェック</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($screeningCompletions as $screeningCompletion)
                            <tr
                                class="align-top transition-colors {{ $screeningCompletion->flow_management_transition ? 'bg-blue-100 text-white' : 'bg-white hover:bg-slate-50' }}"
                                data-screening-completion-id="{{ $screeningCompletion->id }}"
                            >
                                <td class="sticky-col px-3 py-3 whitespace-nowrap">{{ $screeningCompletion->application->created_at->format('Y/m/d H:i') }}</td>
                                <td class="sticky-col px-3 py-3 truncate" title="{{ $screeningCompletion->staff_in_charge }}">{{ $screeningCompletion->staff_in_charge }}</td>
                                <td class="sticky-col sticky-col-last px-3 py-3 whitespace-nowrap">{{ $screeningCompletion->property_name_room }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $screeningCompletion->application_method }}</td>
                                <td class="px-3 py-3 text-center">
                                    <input
                                        type="checkbox"
                                        class="flow-transition-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        @checked($screeningCompletion->flow_management_transition)
                                    >
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($screeningCompletions->hasPages())
            <div class="mt-6 pb-2">
                {{ $screeningCompletions->links('vendor.pagination.admin') }}
            </div>
        @endif
    @endif
@endsection

@push('scripts')
<script>
    function updateRowState(row) {
        const checkbox = row.querySelector('.flow-transition-checkbox');
        const isChecked = checkbox.checked;

        row.classList.remove('bg-white', 'hover:bg-slate-50', 'bg-blue-100', 'text-white');

        if (isChecked) {
            row.classList.add('bg-blue-100', 'text-white');
        } else {
            row.classList.add('bg-white', 'hover:bg-slate-50');
        }

        if (typeof window.refreshAdminStickyColumns === 'function') {
            window.refreshAdminStickyColumns();
        }
    }

    document.querySelectorAll('tbody tr').forEach((row) => {
        updateRowState(row);
    });

    document.querySelectorAll('.flow-transition-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', async (event) => {
            const target = event.target;
            const row = target.closest('tr');
            const screeningCompletionId = row.dataset.screeningCompletionId;
            const previousChecked = !target.checked;
            const value = target.checked ? 1 : 0;

            if (previousChecked) {
                target.checked = true;
                updateRowState(row);

                const confirmed = await window.confirmUncheckTransition();
                if (!confirmed) {
                    return;
                }

                target.checked = false;
                updateRowState(row);
            }

            try {
                const response = await fetch(`/admin/screening-completions/${screeningCompletionId}/flow-transition`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ value }),
                });

                const data = await response.json();

                if (!response.ok) {
                    target.checked = previousChecked;
                    alert(data.message || '更新に失敗しました。もう一度お試しください。');
                }

                updateRowState(row);
            } catch (error) {
                target.checked = previousChecked;
                updateRowState(row);
                alert('更新に失敗しました。もう一度お試しください。');
            }
        });
    });
</script>
@endpush
