@extends('layouts.admin')

@section('title', 'ユーザー管理 — ' . config('app.name'))

@php
    use App\Support\Role;
@endphp

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-900">ユーザー管理</h2>
    <p class="mt-1 text-sm text-slate-500">名前・メールアドレス・パスワード・ロールでユーザーを追加し、ログイン権限を管理します。</p>
</div>

@if($errors->any())
<div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<section class="form-section form-section-clean user-directory-section" data-employee-directory>
    <h2 class="section-label">社員一覧から参照</h2>
    <p class="mt-0 mb-4 text-sm text-slate-500">
        社員ポータルの社員情報を検索し、「選択」で下の追加フォームへ名前・メールを反映します。パスワードは手入力してください。
    </p>

    @if (! ($employeePortalConfigured ?? false))
        <div class="alert alert-error mb-0">
            社員ポータル連携が未設定です。<code>EMPLOYEE_PORTAL_API_URL</code> と <code>EMPLOYEE_PORTAL_PROXY_SECRET</code> を設定してください。
        </div>
    @else
        <form class="user-directory-search" data-employee-directory-form autocomplete="off">
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label for="directory_keyword">キーワード</label>
                    <input
                        type="text"
                        id="directory_keyword"
                        name="keyword"
                        maxlength="100"
                        placeholder="氏名・メール・社員ID・部署"
                        data-employee-directory-keyword
                    >
                </div>
                <div class="form-group">
                    <label for="directory_status">在籍状況</label>
                    <select id="directory_status" name="status" data-employee-directory-status>
                        @foreach (['在籍', '退職', '辞退'] as $statusOption)
                            <option
                                value="{{ $statusOption }}"
                                @selected(($employeePortalDefaults['status'] ?? '在籍') === $statusOption)
                            >{{ $statusOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="directory_department">部署</label>
                    <input
                        type="text"
                        id="directory_department"
                        name="department"
                        maxlength="100"
                        value="{{ $employeePortalDefaults['department'] ?? '不動産' }}"
                        placeholder="例: 不動産"
                        data-employee-directory-department
                    >
                </div>
            </div>
            <div class="form-actions form-actions-inline">
                <button type="submit" class="btn btn-primary" data-employee-directory-submit>検索</button>
            </div>
        </form>

        <p class="mt-3 mb-2 text-sm text-slate-500 hidden" data-employee-directory-message></p>

        <div class="table-wrapper mt-3 hidden" data-employee-directory-results-wrap>
            <table class="data-table user-table">
                <thead>
                    <tr>
                        <th>社員ID</th>
                        <th>名前</th>
                        <th>メール</th>
                        <th>所属</th>
                        <th>在籍状況</th>
                        <th>登録</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody data-employee-directory-results></tbody>
            </table>
        </div>
    @endif
</section>

<section class="form-section form-section-clean user-add-section">
    <h2 class="section-label">ユーザーを追加</h2>
    <form method="post" action="{{ route('users.store') }}" class="user-add-form" autocomplete="off">
        @csrf
        <input type="hidden" name="employee_id" id="new_employee_id" value="{{ old('employee_id') }}" data-employee-directory-employee-id>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label for="new_name">名前 <span class="required">*</span></label>
                <input type="text" id="new_name" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="例: 山田 太郎">
            </div>
            <div class="form-group">
                <label for="new_email">メールアドレス <span class="required">*</span></label>
                <input type="email" id="new_email" name="email" value="{{ old('email') }}" required maxlength="255" placeholder="example@careearth.info">
            </div>
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label for="new_password">パスワード <span class="required">*</span></label>
                <input type="password" id="new_password" name="password" required minlength="8" placeholder="8文字以上">
            </div>
            <div class="form-group">
                <label for="new_role">ロール <span class="required">*</span></label>
                <select id="new_role" name="role" required>
                    @foreach($roles as $value => $label)
                    <option value="{{ $value }}" @selected(old('role', Role::EDITOR) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <span class="block text-sm font-medium text-slate-700 mb-1.5">成績表示</span>
                @php $newShowPerformance = (string) old('show_performance', '1') === '1'; @endphp
                <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-0.5 text-sm" role="group" aria-label="成績表示">
                    <label @class(['rounded-md px-3 py-1.5 font-medium cursor-pointer transition', 'bg-white text-slate-900 shadow-sm' => $newShowPerformance, 'text-slate-500 hover:text-slate-800' => ! $newShowPerformance])>
                        <input type="radio" name="show_performance" value="1" class="sr-only" @checked($newShowPerformance)>
                        ON
                    </label>
                    <label @class(['rounded-md px-3 py-1.5 font-medium cursor-pointer transition', 'bg-white text-slate-900 shadow-sm' => ! $newShowPerformance, 'text-slate-500 hover:text-slate-800' => $newShowPerformance])>
                        <input type="radio" name="show_performance" value="0" class="sr-only" @checked(! $newShowPerformance)>
                        OFF
                    </label>
                </div>
            </div>
        </div>
        <div class="form-actions form-actions-inline">
            <button type="submit" class="btn btn-primary">追加する</button>
        </div>
    </form>
</section>

<div class="mb-3">
    <h2 class="section-label m-0">ユーザー一覧</h2>
</div>

<div class="table-wrapper">
    <table class="data-table user-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>成績表示</th>
                <th>ロール</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            @php
                $formId = 'user-form-'.$user->id;
                $showPerformance = (bool) old('show_performance', $user->show_performance);
            @endphp
            <tr>
                <td class="id-cell">{{ $user->id }}</td>
                <td>
                    <input
                        type="text"
                        name="name"
                        form="{{ $formId }}"
                        value="{{ old('name', $user->name) }}"
                        required
                        maxlength="100"
                        class="password-input"
                        style="min-width: 10rem;"
                        aria-label="名前"
                    >
                </td>
                <td>{{ $user->email }}</td>
                <td>
                    <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-0.5 text-sm" role="group" aria-label="成績表示">
                        <label class="performance-toggle-label rounded-md px-3 py-1.5 font-medium cursor-pointer transition {{ $showPerformance ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                            <input
                                type="radio"
                                name="show_performance"
                                value="1"
                                form="{{ $formId }}"
                                class="sr-only performance-toggle"
                                @checked($showPerformance)
                            >
                            ON
                        </label>
                        <label class="performance-toggle-label rounded-md px-3 py-1.5 font-medium cursor-pointer transition {{ ! $showPerformance ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                            <input
                                type="radio"
                                name="show_performance"
                                value="0"
                                form="{{ $formId }}"
                                class="sr-only performance-toggle"
                                @checked(! $showPerformance)
                            >
                            OFF
                        </label>
                    </div>
                </td>
                <td>
                    <select name="role" class="role-select" form="{{ $formId }}">
                        @foreach($roles as $value => $label)
                        <option value="{{ $value }}" @selected(Role::normalize($user->role) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="actions-cell">
                    <form id="{{ $formId }}" method="post" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-outline btn-sm">更新</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-3 py-6 text-center text-slate-500">
                    ユーザーがまだ登録されていません。
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<p class="user-note">
    名前・成績表示・ロールは「更新」で変更できます。成績表示がONのユーザーはホームの担当者業績一覧に表示されます。メールアドレスはそのままログインに使います。
    <strong>管理者</strong>は開発用ロールで、すべての画面にアクセス・編集できます（本番前に削除予定）。
    <strong>部長</strong>は物件マスターデータ・賃貸管理・ユーザー管理まで編集できます。
    <strong>編集者</strong>は物件マスターデータ一覧とユーザー管理以外を編集できます。
    <strong>閲覧者</strong>は各画面の閲覧のみ可能です。
    無操作が2時間続くと再ログインが必要になります（操作中は切れません）。
</p>
@endsection

@push('scripts')
<script>
    (function () {
        document.querySelectorAll('.performance-toggle').forEach((input) => {
            input.addEventListener('change', () => {
                const group = input.closest('[role="group"]');
                if (!group) {
                    return;
                }

                group.querySelectorAll('.performance-toggle-label').forEach((label) => {
                    const radio = label.querySelector('.performance-toggle');
                    const on = radio?.checked;
                    label.classList.toggle('bg-white', !!on);
                    label.classList.toggle('text-slate-900', !!on);
                    label.classList.toggle('shadow-sm', !!on);
                    label.classList.toggle('text-slate-500', !on);
                    label.classList.toggle('hover:text-slate-800', !on);
                });
            });
        });

        document.querySelectorAll('.user-add-form [name="show_performance"]').forEach((input) => {
            input.addEventListener('change', () => {
                const group = input.closest('[role="group"]');
                if (!group) {
                    return;
                }

                group.querySelectorAll('label').forEach((label) => {
                    const radio = label.querySelector('input[type="radio"]');
                    const on = radio?.checked;
                    label.classList.toggle('bg-white', !!on);
                    label.classList.toggle('text-slate-900', !!on);
                    label.classList.toggle('shadow-sm', !!on);
                    label.classList.toggle('text-slate-500', !on);
                    label.classList.toggle('hover:text-slate-800', !on);
                });
            });
        });

        const root = document.querySelector('[data-employee-directory]');
        const form = root?.querySelector('[data-employee-directory-form]');
        if (!root || !form) {
            return;
        }

        const endpoint = @json(route('users.employee-directory'));
        const resultsWrap = root.querySelector('[data-employee-directory-results-wrap]');
        const resultsBody = root.querySelector('[data-employee-directory-results]');
        const messageEl = root.querySelector('[data-employee-directory-message]');
        const submitBtn = root.querySelector('[data-employee-directory-submit]');
        const nameInput = document.getElementById('new_name');
        const emailInput = document.getElementById('new_email');
        const employeeIdInput = document.getElementById('new_employee_id');

        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');

        const setMessage = (text, isError = false) => {
            if (!messageEl) {
                return;
            }
            messageEl.textContent = text || '';
            messageEl.classList.toggle('hidden', !text);
            messageEl.classList.toggle('text-red-600', !!isError);
            messageEl.classList.toggle('text-slate-500', !isError);
        };

        const affiliation = (employee) => {
            return [employee.department, employee.section].filter(Boolean).join(' / ') || '—';
        };

        const renderRows = (employees) => {
            if (!resultsBody || !resultsWrap) {
                return;
            }

            if (!employees.length) {
                resultsBody.innerHTML = '<tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">該当する社員が見つかりませんでした。</td></tr>';
                resultsWrap.classList.remove('hidden');
                return;
            }

            resultsBody.innerHTML = employees.map((employee) => {
                const registered = !!employee.already_registered;
                const selectDisabled = registered ? 'disabled' : '';
                const registeredLabel = registered
                    ? '<span class="text-amber-700 text-xs font-medium">登録済み</span>'
                    : '<span class="text-slate-400 text-xs">未登録</span>';
                const payload = escapeHtml(JSON.stringify({
                    name: employee.name || '',
                    email: employee.email || '',
                    employee_id: employee.employee_id || '',
                }));

                return `
                    <tr>
                        <td>${escapeHtml(employee.employee_id || '—')}</td>
                        <td>${escapeHtml(employee.name || '—')}</td>
                        <td>${escapeHtml(employee.email || '—')}</td>
                        <td>${escapeHtml(affiliation(employee))}</td>
                        <td>${escapeHtml(employee.employment_status || '—')}</td>
                        <td>${registeredLabel}</td>
                        <td class="actions-cell">
                            <button
                                type="button"
                                class="btn btn-outline btn-sm"
                                data-employee-directory-select
                                data-employee="${payload}"
                                ${selectDisabled}
                            >選択</button>
                        </td>
                    </tr>
                `;
            }).join('');

            resultsWrap.classList.remove('hidden');
        };

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            setMessage('検索中…');
            if (submitBtn) {
                submitBtn.disabled = true;
            }

            const params = new URLSearchParams();
            const keyword = form.querySelector('[data-employee-directory-keyword]')?.value?.trim() || '';
            const status = form.querySelector('[data-employee-directory-status]')?.value?.trim() || '';
            const department = form.querySelector('[data-employee-directory-department]')?.value?.trim() || '';
            if (keyword) params.set('keyword', keyword);
            if (status) params.set('status', status);
            if (department) params.set('department', department);

            try {
                const response = await fetch(`${endpoint}?${params.toString()}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    setMessage(data.message || '社員一覧の取得に失敗しました。', true);
                    if (resultsWrap) {
                        resultsWrap.classList.add('hidden');
                    }
                    return;
                }

                const employees = Array.isArray(data.employees) ? data.employees : [];
                const count = data.meta?.count ?? employees.length;
                setMessage(`検索結果: ${count} 件`);
                renderRows(employees);
            } catch (error) {
                setMessage('社員一覧の取得に失敗しました。', true);
                if (resultsWrap) {
                    resultsWrap.classList.add('hidden');
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            }
        });

        // 手動で名前・メールを変えたら社員ID紐付けをクリア（競合防止）
        let suppressEmployeeIdClear = false;
        const clearEmployeeIdOnManualEdit = () => {
            if (suppressEmployeeIdClear) {
                return;
            }
            if (employeeIdInput) {
                employeeIdInput.value = '';
            }
        };
        nameInput?.addEventListener('input', clearEmployeeIdOnManualEdit);
        emailInput?.addEventListener('input', clearEmployeeIdOnManualEdit);

        root.addEventListener('click', (event) => {
            const button = event.target.closest('[data-employee-directory-select]');
            if (!button || button.disabled) {
                return;
            }

            let payload = {};
            try {
                payload = JSON.parse(button.getAttribute('data-employee') || '{}');
            } catch (error) {
                return;
            }

            suppressEmployeeIdClear = true;
            if (nameInput) {
                nameInput.value = payload.name || '';
            }
            if (emailInput) {
                emailInput.value = payload.email || '';
            }
            if (employeeIdInput) {
                employeeIdInput.value = payload.employee_id || '';
            }
            suppressEmployeeIdClear = false;

            setMessage(`「${payload.name || payload.email || '社員'}」を追加フォームに反映しました。パスワードを入力して追加してください。`);
            nameInput?.focus();
            document.querySelector('.user-add-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    })();
</script>
@endpush
