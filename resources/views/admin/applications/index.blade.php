@extends('layouts.admin')

@section('title', '申込一覧 — ' . config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">申込一覧</h2>
            <p class="mt-1 text-sm text-slate-500">
                表示件数: <strong class="text-slate-700">{{ $applications->total() }}</strong> 件
                @if ($search !== '')
                    <span class="text-slate-400">（「{{ $search }}」で絞り込み中）</span>
                @endif
            </p>
            <p class="mt-1 text-xs text-slate-400">審査ＯＫの申込は書類管理へ移ります。キャンセルは一覧から非表示になります。</p>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2 w-full sm:w-auto">
            @if ($canEdit ?? false)
            <a href="{{ route('admin.applications.create') }}" class="btn btn-primary btn-sm shrink-0">+ 新規申込</a>
            @endif
            <x-admin-search-form :value="$search" />
        </div>
    </div>

    @if ($applications->isEmpty())
        <div class="empty-state">
            <span class="empty-icon">📋</span>
            @if ($search !== '')
                <h2>条件に一致する申込がありません</h2>
                <p>「{{ $search }}」に一致する申込がありません。</p>
            @else
                <h2>表示する申込がありません</h2>
                <p>「新規申込」から登録してください。</p>
                @if ($canEdit ?? false)
                <a href="{{ route('admin.applications.create') }}" class="btn btn-primary">申込を登録する</a>
                @endif
            @endif
        </div>
    @else
        <div class="application-blocks-board">
            <div class="application-blocks-grid">
                @foreach ($applications as $application)
                    <article
                        class="application-block @if ($application->sales_action_required) application-block--action-required @endif"
                        data-application-id="{{ $application->id }}"
                    >
                        <div class="application-block__header">
                            <div>
                                <h3 class="application-block__title">{{ $application->propertyNameRoomLabel() }}</h3>
                                <p class="application-block__subtitle">{{ $application->created_at->format('Y/m/d H:i') }}</p>
                            </div>
                            <div class="application-block__badges">
                                @if ($application->sales_action_required)
                                    <span class="application-block__badge application-block__badge--warn">営業要対応</span>
                                @endif
                                @if ($application->screening_ok)
                                    <span class="application-block__badge application-block__badge--ok">審査ＯＫ</span>
                                @endif
                            </div>
                        </div>

                        <div class="application-block__body">
                            <div class="application-block__cells">
                                <div class="application-block__cell">
                                    <span class="application-block__cell-label">顧客ID</span>
                                    <div class="application-block__cell-value">
                                        @if ($application->customer?->case_number)
                                            <a
                                                href="{{ route('admin.customers.index', ['search' => $application->customer->case_number]) }}"
                                                class="text-primary-600 hover:underline font-medium"
                                                title="顧客一覧で表示"
                                            >{{ $application->customer->displayCustomerId() }}</a>
                                        @else
                                            <span class="text-slate-400">未登録</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="application-block__cell">
                                    <span class="application-block__cell-label">担当者</span>
                                    <div class="application-block__cell-value">{{ $application->staff_in_charge ?: '—' }}</div>
                                </div>
                                <div class="application-block__cell">
                                    <span class="application-block__cell-label">入居予定</span>
                                    <div class="application-block__cell-value">{{ $application->displayMoveInDate() ?? '—' }}</div>
                                </div>
                                <div class="application-block__cell">
                                    <span class="application-block__cell-label">管理会社</span>
                                    <div class="application-block__cell-value">{{ $application->displayManagementCompanyName() }}</div>
                                </div>
                                <div class="application-block__cell">
                                    <span class="application-block__cell-label">記入方法</span>
                                    <div class="application-block__cell-value">{{ $application->displayEntryMethod() }}</div>
                                </div>
                            </div>

                            <div class="application-block__cells application-block__cells--stack">
                                <label class="application-block__cell application-block__cell--editable">
                                    <span class="application-block__cell-label">状況</span>
                                    <textarea
                                        class="application-inline-field"
                                        rows="2"
                                        maxlength="2000"
                                        data-field="status"
                                        data-label="状況"
                                        placeholder="状況を入力"
                                        @readonly(!($canEdit ?? false))
                                    >{{ $application->status }}</textarea>
                                </label>
                                <label class="application-block__cell application-block__cell--editable">
                                    <span class="application-block__cell-label">MEMO</span>
                                    <textarea
                                        class="application-inline-field"
                                        rows="2"
                                        maxlength="2000"
                                        data-field="memo"
                                        data-label="MEMO"
                                        placeholder="MEMOを入力"
                                        @readonly(!($canEdit ?? false))
                                    >{{ $application->memo }}</textarea>
                                </label>
                            </div>
                        </div>

                        <div class="application-block__flags">
                            <label class="application-block__flag exclusive-cell" data-exclusive-for="sales_action_required">
                                <input
                                    type="checkbox"
                                    class="application-flag-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                    data-field="sales_action_required"
                                    @checked($application->sales_action_required)
                                    @disabled(!($canEdit ?? false))
                                >
                                <span>営業要対応</span>
                            </label>
                            <label class="application-block__flag exclusive-cell" data-exclusive-for="screening_ok">
                                <input
                                    type="checkbox"
                                    class="application-flag-checkbox screening-ok-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                    data-field="screening_ok"
                                    @checked($application->screening_ok)
                                    @disabled(!($canEdit ?? false))
                                >
                                <span>審査ＯＫ</span>
                            </label>
                            <label class="application-block__flag exclusive-cell" data-exclusive-for="is_cancelled">
                                <input
                                    type="checkbox"
                                    class="application-flag-checkbox cancel-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                    data-field="is_cancelled"
                                    @checked($application->is_cancelled)
                                    @disabled(!($canEdit ?? false))
                                >
                                <span>キャンセル</span>
                            </label>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        @if ($applications->hasPages())
            <div class="mt-6 pb-2">
                {{ $applications->links('vendor.pagination.admin') }}
            </div>
        @endif
    @endif
@endsection

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

    function removeBlock(block) {
        block.classList.add('is-removing');
        setTimeout(() => block.remove(), 450);
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
            const block = checkbox.closest('[data-application-id]');
            const applicationId = block?.dataset.applicationId;
            if (!applicationId) {
                return;
            }

            const field = checkbox.dataset.field;
            const screeningOk = block.querySelector('[data-field="screening_ok"]');
            const cancel = block.querySelector('[data-field="is_cancelled"]');

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
                    removeBlock(block);
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
            const block = field.closest('[data-application-id]');
            const applicationId = block?.dataset.applicationId;
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
