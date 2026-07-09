@extends('layouts.admin')

@section('title', '申込一覧 — ' . config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">申込一覧</h2>
            <p class="mt-1 text-sm text-slate-500">審査ＯＫの申込は書類管理へ移ります。キャンセルは一覧から非表示になります。</p>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2 w-full sm:w-auto">
            @if ($canEdit ?? false)
            <a href="{{ route('admin.applications.create') }}" class="btn btn-primary btn-sm shrink-0">+ 新規申込</a>
            @endif
            <x-admin-search-form :value="$search" />
        </div>
    </div>

    @if ($applications->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center text-slate-500">
            @if ($search !== '')
                <p>「{{ $search }}」に一致する申込がありません。</p>
            @else
                <p>表示する申込がありません。</p>
                @if ($canEdit ?? false)
                <a href="{{ route('admin.applications.create') }}" class="btn btn-primary mt-4">申込を登録する</a>
                @endif
            @endif
        </div>
    @else
        <div class="application-list-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="admin-table-scroll overflow-x-auto">
                <table class="application-list-table admin-table-sticky min-w-full text-sm text-left" data-sticky-cols="4">
                    <thead>
                        <tr>
                            <th class="sticky-col px-3 py-3 font-semibold whitespace-nowrap min-w-[88px]">顧客ID</th>
                            <th class="sticky-col px-3 py-3 font-semibold whitespace-nowrap min-w-[130px]">作成日時</th>
                            <th class="sticky-col px-3 py-3 font-semibold whitespace-nowrap min-w-[100px]">担当者</th>
                            <th class="sticky-col sticky-col-last px-3 py-3 font-semibold whitespace-nowrap min-w-[180px]">物件名＋号室</th>
                            <th class="px-3 py-3 font-semibold whitespace-nowrap min-w-[110px]">入居予定日</th>
                            <th class="px-3 py-3 font-semibold whitespace-nowrap min-w-[90px] text-right">広告料</th>
                            <th class="px-3 py-3 font-semibold whitespace-nowrap min-w-[140px]">管理会社名</th>
                            <th class="px-3 py-3 font-semibold whitespace-nowrap min-w-[110px]">申込方法</th>
                            <th class="px-3 py-3 font-semibold whitespace-nowrap min-w-[180px]">状況</th>
                            <th class="px-3 py-3 font-semibold whitespace-nowrap min-w-[200px]">MEMO</th>
                            <th class="px-3 py-3 font-semibold whitespace-nowrap min-w-[120px]">物件資料</th>
                            <th class="px-3 py-3 font-semibold whitespace-nowrap min-w-[180px]">家電サポート・CB等</th>
                            <th class="px-3 py-3 font-semibold whitespace-nowrap min-w-[90px] text-center">営業要対応</th>
                            <th class="px-3 py-3 font-semibold whitespace-nowrap min-w-[80px] text-center">審査ＯＫ</th>
                            <th class="px-3 py-3 font-semibold whitespace-nowrap min-w-[80px] text-center">キャンセル</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($applications as $application)
                            <tr
                                class="application-list-row align-top"
                                data-application-id="{{ $application->id }}"
                            >
                                <td class="sticky-col px-3 py-3 whitespace-nowrap">
                                    @if ($application->customer?->case_number)
                                        <a
                                            href="{{ route('admin.customers.index', ['search' => $application->customer->case_number]) }}"
                                            class="text-primary-600 hover:underline font-medium"
                                            title="顧客一覧で表示"
                                        >{{ $application->customer->displayCustomerId() }}</a>
                                    @else
                                        <span class="text-slate-400">未登録</span>
                                    @endif
                                </td>
                                <td class="sticky-col px-3 py-3 whitespace-nowrap text-slate-700">
                                    {{ $application->created_at->format('Y/m/d H:i') }}
                                </td>
                                <td class="sticky-col px-3 py-3 whitespace-nowrap">{{ $application->staff_in_charge ?: '—' }}</td>
                                <td class="sticky-col sticky-col-last px-3 py-3">
                                    <div class="font-medium text-slate-900">{{ $application->propertyNameRoomLabel() }}</div>
                                    @if ($application->customer && ($application->property_name === null || $application->room_number === null))
                                        <div class="mt-0.5 text-xs text-slate-400">顧客情報参照</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $application->displayMoveInDate() ?? '—' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap text-right tabular-nums">
                                    {{ $application->formattedAdvertisingFee() }}
                                </td>
                                <td class="px-3 py-3">{{ $application->displayManagementCompanyName() }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $application->application_method ?: '—' }}</td>
                                <td class="px-3 py-3 min-w-[180px]">
                                    <textarea
                                        class="application-inline-field w-full min-h-[2.5rem] rounded-lg border border-slate-200 px-2 py-1.5 text-sm resize-y focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        rows="2"
                                        maxlength="2000"
                                        data-field="status"
                                        data-label="状況"
                                        placeholder="状況を入力"
                                        @readonly(!($canEdit ?? false))
                                    >{{ $application->status }}</textarea>
                                </td>
                                <td class="px-3 py-3 min-w-[200px]">
                                    <textarea
                                        class="application-inline-field w-full min-h-[2.5rem] rounded-lg border border-slate-200 px-2 py-1.5 text-sm resize-y focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        rows="2"
                                        maxlength="2000"
                                        data-field="memo"
                                        data-label="MEMO"
                                        placeholder="MEMOを入力"
                                        @readonly(!($canEdit ?? false))
                                    >{{ $application->memo }}</textarea>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @if ($application->property_documents_url)
                                        <a
                                            href="{{ $application->property_documents_url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-primary-600 hover:underline break-all"
                                        >資料を開く</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-3 min-w-[180px]">
                                    <textarea
                                        class="application-inline-field w-full min-h-[2.5rem] rounded-lg border border-slate-200 px-2 py-1.5 text-sm resize-y focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        rows="2"
                                        maxlength="2000"
                                        data-field="appliance_support_notes"
                                        data-label="家電サポート・CB等"
                                        placeholder="特記事項を入力"
                                        @readonly(!($canEdit ?? false))
                                    >{{ $application->appliance_support_notes }}</textarea>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <input
                                        type="checkbox"
                                        class="application-flag-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        data-field="sales_action_required"
                                        @checked($application->sales_action_required)
                                        @disabled(!($canEdit ?? false))
                                    >
                                </td>
                                <td class="px-3 py-3 text-center exclusive-cell" data-exclusive-for="screening_ok">
                                    <input
                                        type="checkbox"
                                        class="application-flag-checkbox screening-ok-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        data-field="screening_ok"
                                        @checked($application->screening_ok)
                                        @disabled(!($canEdit ?? false))
                                    >
                                </td>
                                <td class="px-3 py-3 text-center exclusive-cell" data-exclusive-for="is_cancelled">
                                    <input
                                        type="checkbox"
                                        class="application-flag-checkbox cancel-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        data-field="is_cancelled"
                                        @checked($application->is_cancelled)
                                        @disabled(!($canEdit ?? false))
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

@push('head')
    <style>
        .application-list-table thead {
            background: #f8fafc;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
        }

        .application-list-row:nth-child(even) td {
            background-color: #fafbfc;
        }

        .application-list-row:hover td {
            background-color: #f1f5f9;
        }

        .application-list-row.is-removing {
            pointer-events: none;
            animation: application-row-remove 0.45s ease-out forwards;
        }

        @keyframes application-row-remove {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(8px); }
        }
    </style>
@endpush

@if ($canEdit ?? false)
@push('scripts')
<script>
    const exclusiveMessages = {
        screening_ok: 'キャンセルが選択されているため、審査ＯＫは設定できません。先にキャンセルのチェックを外してください。',
        is_cancelled: '審査ＯＫが選択されているため、キャンセルは設定できません。先に審査ＯＫのチェックを外してください。',
    };

    function applicationFlagsUrl(id) {
        return adminApiUrl('/admin/applications/' + encodeURIComponent(id) + '/flags');
    }

    function applicationFieldUrl(id) {
        return adminApiUrl('/admin/applications/' + encodeURIComponent(id) + '/fields');
    }

    function removeRow(row) {
        row.classList.add('is-removing');
        setTimeout(() => row.remove(), 450);
    }

    document.querySelectorAll('.exclusive-cell').forEach((cell) => {
        cell.addEventListener('click', (event) => {
            if (event.target.type === 'checkbox') {
                return;
            }

            const checkbox = cell.querySelector('input[type="checkbox"]');
            if (checkbox && !checkbox.disabled) {
                checkbox.click();
            }
        });
    });

    document.querySelectorAll('.application-flag-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', async () => {
            const row = checkbox.closest('tr');
            const applicationId = row?.dataset.applicationId;
            if (!applicationId) {
                return;
            }

            const field = checkbox.dataset.field;
            const screeningOk = row.querySelector('[data-field="screening_ok"]');
            const cancel = row.querySelector('[data-field="is_cancelled"]');

            if (checkbox.checked) {
                if (field === 'screening_ok' && cancel?.checked) {
                    checkbox.checked = false;
                    alert(exclusiveMessages.screening_ok);
                    return;
                }

                if (field === 'is_cancelled' && screeningOk?.checked) {
                    checkbox.checked = false;
                    alert(exclusiveMessages.is_cancelled);
                    return;
                }
            }

            try {
                const response = await fetch(applicationFlagsUrl(applicationId), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ field, value: checkbox.checked }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    checkbox.checked = !checkbox.checked;
                    alert(data.message || '更新に失敗しました。');
                    return;
                }

                if (data.remove_row) {
                    removeRow(row);
                }
            } catch (error) {
                checkbox.checked = !checkbox.checked;
                alert('更新に失敗しました。もう一度お試しください。');
            }
        });
    });

    document.querySelectorAll('.application-inline-field').forEach((field) => {
        let previousValue = field.value;

        field.addEventListener('blur', async () => {
            const row = field.closest('tr');
            const applicationId = row?.dataset.applicationId;
            const fieldName = field.dataset.field;
            const fieldLabel = field.dataset.label || fieldName;

            if (!applicationId || field.value === previousValue) {
                return;
            }

            try {
                const response = await fetch(applicationFieldUrl(applicationId), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ field: fieldName, value: field.value || null }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    field.value = previousValue;
                    alert(data.message || `${fieldLabel}の保存に失敗しました。`);
                    return;
                }

                previousValue = field.value;
            } catch (error) {
                field.value = previousValue;
                alert(`${fieldLabel}の保存に失敗しました。`);
            }
        });
    });
</script>
@endpush
@endif
