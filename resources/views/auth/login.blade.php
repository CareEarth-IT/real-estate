@extends('layouts.rental')

@section('title', 'ログイン — ' . config('app.name'))

@section('content')
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8">
            <h2 class="text-2xl font-bold text-slate-900 text-center">ログイン</h2>
            <p class="mt-3 text-sm text-slate-600 text-center leading-relaxed">
                登録済みのメールアドレスとパスワードでサインインしてください。
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

            <form method="post" action="{{ route('login.attempt') }}" class="mt-8 space-y-4" autocomplete="off">
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

            <p class="mt-6 text-xs text-slate-500 text-center leading-relaxed">
                アカウントはユーザー管理画面で追加できます。<br>
                無操作が2時間続くと再ログインが必要です。
            </p>
        </div>
    </div>
@endsection
