@extends('layouts.admin')

@section('title', '申込一覧 - 管理画面')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-2xl font-bold text-slate-900">申込一覧</h2>
        <x-admin-search-form :value="$search" />
    </div>

    @if ($applications->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center text-slate-500">
            @if ($search !== '')
                「{{ $search }}」に一致する申込データがありません。
            @else
                表示する申込データがありません。
            @endif
        </div>
    @else
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="resizable-table admin-table-sticky min-w-full text-sm text-left" data-table-id="applications" data-sticky-cols="3">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="sticky-col px-3 py-3 font-medium whitespace-nowrap min-w-[130px]">作成日時</th>
                            <th class="sticky-col px-3 py-3 font-medium whitespace-nowrap min-w-[90px]">担当者</th>
                            <th class="sticky-col sticky-col-last px-3 py-3 font-medium whitespace-nowrap min-w-[160px]">物件名＋部屋番号</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">入居予定日</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">広告料</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap text-center">仲介手数料</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">仲介手数料（金額）</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">管理会社名</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">申込方法</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">状況</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">MEMO</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">物件資料</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap">家電サポート・CB等</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap text-center">営業要対応</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap text-center">審査ＯＫ</th>
                            <th class="px-3 py-3 font-medium whitespace-nowrap text-center">キャンセル</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($applications as $application)
                            <tr
                                class="align-top transition-colors {{ $application->is_cancelled ? 'bg-neutral-400 text-neutral-800' : ($application->screening_ok ? 'bg-blue-100 text-white' : 'bg-white hover:bg-slate-50') }}"
                                data-application-id="{{ $application->id }}"
                            >
                                <td class="sticky-col px-3 py-3 whitespace-nowrap">{{ $application->created_at->format('Y/m/d H:i') }}</td>
                                <td class="sticky-col px-3 py-3 whitespace-nowrap">{{ $application->staff_in_charge }}</td>
                                <td class="sticky-col sticky-col-last px-3 py-3 whitespace-nowrap">{{ $application->property_name_room }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $application->scheduled_move_in_date?->format('Y/m/d') }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ number_format($application->advertising_fee) }}</td>
                                <td class="px-3 py-3 whitespace-nowrap text-center">{{ $application->has_broker_fee ? 'あり' : 'なし' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $application->has_broker_fee ? number_format((int) $application->broker_fee) : '—' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $application->management_company_name }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $application->application_method }}</td>
                                <td class="px-3 py-3 max-w-[200px] whitespace-pre-line">{{ $application->status }}</td>
                                <td class="px-3 py-3 max-w-[200px]">{{ $application->memo ?? '—' }}</td>
                                <td class="px-3 py-3 max-w-[150px]">
                                    @if ($application->property_documents_url)
                                        <a href="{{ $application->property_documents_url }}" target="_blank" rel="noopener" class="text-primary-600 hover:underline break-all admin-checked-link">リンク</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-3 max-w-[200px]">{{ $application->appliance_support_notes ?? '—' }}</td>
                                <td class="px-3 py-3 text-center">
                                    <input
                                        type="checkbox"
                                        class="flag-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        data-field="sales_action_required"
                                        @checked($application->sales_action_required)
                                    >
                                </td>
                                <td class="px-3 py-3 text-center exclusive-cell" data-exclusive-for="screening_ok">
                                    <input
                                        type="checkbox"
                                        class="flag-checkbox screening-ok-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        data-field="screening_ok"
                                        @checked($application->screening_ok)
                                        @disabled($application->is_cancelled && ! $application->screening_ok)
                                    >
                                </td>
                                <td class="px-3 py-3 text-center exclusive-cell" data-exclusive-for="is_cancelled">
                                    <input
                                        type="checkbox"
                                        class="flag-checkbox cancel-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        data-field="is_cancelled"
                                        @checked($application->is_cancelled)
                                        @disabled($application->screening_ok && ! $application->is_cancelled)
                                    >
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($applications->hasPages())
            <div class="mt-6 pb-2">
                {{ $applications->links('vendor.pagination.admin') }}
            </div>
        @endif
    @endif
@endsection

@push('scripts')
<script>
    const exclusiveMessages = {
        screening_ok: 'キャンセルが選択されているため、審査ＯＫは設定できません。先にキャンセルのチェックを外してください。',
        is_cancelled: '審査ＯＫが選択されているため、キャンセルは設定できません。先に審査ＯＫのチェックを外してください。',
    };

    function updateRowState(row) {
        const screeningOk = row.querySelector('.screening-ok-checkbox');
        const cancel = row.querySelector('.cancel-checkbox');

        const isScreeningOk = screeningOk.checked;
        const isCancelled = cancel.checked;

        row.classList.remove('bg-white', 'hover:bg-slate-50', 'bg-blue-100', 'text-white', 'bg-neutral-400', 'text-neutral-800');

        if (isCancelled) {
            row.classList.add('bg-neutral-400', 'text-neutral-800');
        } else if (isScreeningOk) {
            row.classList.add('bg-blue-100', 'text-white');
        } else {
            row.classList.add('bg-white', 'hover:bg-slate-50');
        }

        screeningOk.disabled = isCancelled && !isScreeningOk;
        cancel.disabled = isScreeningOk && !isCancelled;

        if (typeof window.refreshAdminStickyColumns === 'function') {
            window.refreshAdminStickyColumns();
        }
    }

    document.querySelectorAll('tbody tr').forEach((row) => {
        updateRowState(row);

        row.querySelectorAll('.exclusive-cell').forEach((cell) => {
            cell.addEventListener('click', (event) => {
                const checkbox = cell.querySelector('.flag-checkbox');
                if (checkbox.disabled) {
                    event.preventDefault();
                    alert(exclusiveMessages[checkbox.dataset.field]);
                }
            });
        });
    });

    document.querySelectorAll('.flag-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', async (event) => {
            const target = event.target;
            const row = target.closest('tr');
            const applicationId = row.dataset.applicationId;
            const field = target.dataset.field;
            const previousChecked = !target.checked;
            const value = target.checked ? 1 : 0;

            const screeningOk = row.querySelector('.screening-ok-checkbox');
            const cancel = row.querySelector('.cancel-checkbox');

            if (target.checked) {
                if (field === 'screening_ok' && cancel.checked) {
                    target.checked = false;
                    alert(exclusiveMessages.screening_ok);
                    return;
                }
                if (field === 'is_cancelled' && screeningOk.checked) {
                    target.checked = false;
                    alert(exclusiveMessages.is_cancelled);
                    return;
                }
            }

            if (field === 'screening_ok' && previousChecked) {
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
                const response = await fetch(`/admin/applications/${applicationId}/flags`, {
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
                    alert(data.message || '更新に失敗しました。もう一度お試しください。');
                    return;
                }

                if (typeof data.screening_ok === 'boolean') {
                    screeningOk.checked = data.screening_ok;
                }
                if (typeof data.is_cancelled === 'boolean') {
                    cancel.checked = data.is_cancelled;
                }

                updateRowState(row);
            } catch (error) {
                target.checked = previousChecked;
                alert('更新に失敗しました。もう一度お試しください。');
            } finally {
                updateRowState(row);
            }
        });
    });
</script>
@endpush
