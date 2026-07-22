@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8 text-center max-w-lg mx-auto">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#d4edf7] text-[#2d6b8a]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-slate-900">登録が完了しました</h2>
        <p class="mt-3 text-sm text-slate-600">申込情報の登録が完了しました。</p>

        <div class="mt-6 space-y-4 text-left">
            <div class="rounded-lg bg-slate-50 border border-slate-200 px-6 py-4">
                <p class="text-sm text-slate-500">受付番号</p>
                <p class="mt-1 text-2xl font-bold text-primary-700">{{ $application->id }}</p>
            </div>
            <div class="rounded-lg bg-slate-50 border border-slate-200 px-6 py-4">
                <p class="text-sm text-slate-500">作成日時</p>
                <p class="mt-1 text-lg font-semibold text-slate-800">{{ $application->created_at->format('Y/m/d H:i') }}</p>
            </div>
        </div>

        <div class="mt-8">
            <a
                href="{{ route('applications.create') }}"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
            >
                新しい申込を入力する
            </a>
        </div>
    </div>
@endsection
