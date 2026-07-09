@extends('layouts.admin')

@section('title', 'ユーザー管理 — ' . config('app.name'))

@php
    use App\Support\Role;
@endphp

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-900">ユーザー管理</h2>
    <p class="mt-1 text-sm text-slate-500">ログインユーザーのロールを変更できます（管理者 / 部長 / 編集者 / 閲覧者）</p>
</div>


@if($errors->any())
<div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<section class="form-section form-section-clean user-add-section">
    <h2 class="section-label">ユーザーを追加</h2>
    <form method="post" action="{{ route('users.store') }}" class="user-add-form">
        @csrf
        <div class="form-row form-row-3">
            <div class="form-group">
                <label for="new_email">メールアドレス <span class="required">*</span></label>
                <input type="email" id="new_email" name="email" value="{{ old('email') }}" required>
            </div>
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
        <div class="form-actions form-actions-inline">
            <button type="submit" class="btn btn-primary">追加する</button>
        </div>
    </form>
</section>

<div class="table-wrapper">
    <table class="data-table user-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>メールアドレス</th>
                <th>ロール</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            @php $formId = 'user-form-'.$user->id; @endphp
            <tr>
                <td class="id-cell">{{ $user->id }}</td>
                <td>{{ $user->email }}</td>
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
            @endforeach
        </tbody>
    </table>
</div>

<p class="user-note">
    ロール変更は次のページ表示から反映されます。
    <strong>管理者</strong>は開発用ロールで、すべての画面にアクセス・編集できます（本番前に削除予定）。
    <strong>部長</strong>は物件マスターデータ・賃貸管理・ユーザー管理まで編集できます。
    <strong>編集者</strong>は物件マスターデータ一覧とユーザー管理以外を編集できます。
    <strong>閲覧者</strong>は各画面の閲覧のみ可能です。
</p>
@endsection
