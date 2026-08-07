@extends('layouts.rental')

@section('title', 'ログイン — ' . config('app.name'))

@section('content')
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8">
            <h2 class="text-2xl font-bold text-slate-900 text-center">ログイン</h2>
            <p class="mt-3 text-sm text-slate-600 text-center leading-relaxed">
                @if ($ssoEnabled ?? false)
                    社員ポータルのアカウントでサインインできます。
                @else
                    登録済みのメールアドレスとパスワードでサインインしてください。
                @endif
            </p>

            @if (session('success'))
                <div class="mt-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @error('email')
                <div class="mt-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
                    {{ $message }}
                </div>
            @enderror

            @if (($ssoEnabled ?? false) && ! empty($portalLoginUrl))
                <div class="mt-8">
                    <a
                        href="{{ route('auth.portal.redirect') }}"
                        class="flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors"
                    >
                        社員ポータルからログイン
                    </a>
                </div>
            @elseif ($ssoEnabled ?? false)
                <div class="mt-6 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-amber-900 text-sm">
                    SSO は有効ですが <code>EMPLOYEE_PORTAL_LOGIN_URL</code> が未設定です。
                </div>
            @endif

            @if ($localLoginAllowed ?? true)
                @if ($ssoEnabled ?? false)
                    <div class="mt-8 flex items-center gap-3 text-xs text-slate-400">
                        <span class="h-px flex-1 bg-slate-200"></span>
                        <span>またはローカルログイン</span>
                        <span class="h-px flex-1 bg-slate-200"></span>
                    </div>
                @endif

                <form method="post" action="{{ route('login.attempt') }}" class="mt-6 space-y-4" autocomplete="off">
                    @csrf
                    @if (! empty($redirect))
                        <input type="hidden" name="redirect" value="{{ $redirect }}">
                    @endif
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">メールアドレス</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            value="{{ old('email') }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        >
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">パスワード</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        >
                    </div>
                    <button
                        type="submit"
                        class="flex w-full items-center justify-center rounded-lg bg-primary-600 px-4 py-3 text-sm font-semibold text-white hover:bg-primary-700 transition-colors"
                    >
                        ログイン
                    </button>
                </form>
            @endif

            <p class="mt-6 text-xs text-slate-500 text-center leading-relaxed">
                @if ($ssoEnabled ?? false)
                    初回ログイン時は閲覧者ロールでアカウントが作成されます。権限変更はユーザー管理で行います。<br>
                @else
                    アカウントはユーザー管理画面で追加できます。<br>
                @endif
                無操作が続くと再ログインが必要です。
            </p>
        </div>
    </div>
@endsection
