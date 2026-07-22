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

<section class="form-section form-section-clean user-add-section">
    <h2 class="section-label">ユーザーを追加</h2>
    <form method="post" action="{{ route('users.store') }}" class="user-add-form" autocomplete="off">
        @csrf
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
    })();
</script>
@endpush
