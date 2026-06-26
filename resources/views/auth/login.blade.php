@extends('layouts.rental')

@section('title', 'ログイン — ' . config('app.name'))

@section('content')
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8">
            <h2 class="text-2xl font-bold text-slate-900 text-center">マスターデータ<br>アクセス認証</h2>
            <p class="mt-3 text-sm text-slate-600 text-center leading-relaxed">
                許可されたアカウントでのみアクセスできます
            </p>

            @error('email')
                <div class="mt-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
                    {{ $message }}
                </div>
            @enderror

            <form method="post" action="{{ route('login') }}" class="mt-8 space-y-4" autocomplete="off">
                @csrf
                <input type="hidden" name="redirect" value="{{ $redirect }}">
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

            <p class="mt-6 text-xs text-slate-500 text-center">
                許可アカウント: <strong>{{ config('careearth.allowed_email') }}</strong>
            </p>
        </div>
    </div>
@endsection
