@extends('layouts.rental')

@section('title', 'アクセス案内 — ' . config('app.name'))

@section('content')
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8 text-center">
            <h2 class="text-2xl font-bold text-slate-900">ログイン画面は利用できません</h2>
            <p class="mt-4 text-sm text-slate-600 leading-relaxed">
                Care Earth Home へのアクセスは、社員ポータル経由で行ってください。
            </p>
            @if (! empty($portalLoginUrl))
                <div class="mt-8">
                    <a
                        href="{{ $portalLoginUrl }}"
                        class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors"
                    >
                        社員ポータルへ
                    </a>
                </div>
            @else
                <p class="mt-6 text-sm text-slate-500 leading-relaxed">
                    社員ポータルのURLが未設定です。<code class="text-xs">EMPLOYEE_PORTAL_LOGIN_URL</code> を設定してください。
                </p>
            @endif
        </div>
    </div>
@endsection
