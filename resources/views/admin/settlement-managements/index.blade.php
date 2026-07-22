@extends('layouts.admin')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">決済金管理</h2>
            <p class="mt-1 text-sm text-slate-500">
                表示件数: <strong class="text-slate-700">{{ $settlementManagements->total() }}</strong> 件
                @if ($search !== '')
                    <span class="text-slate-400">（「{{ $search }}」で絞り込み中）</span>
                @endif
            </p>
        </div>
        <x-admin-search-form :value="$search" />
    </div>

    @if ($settlementManagements->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-12 text-center text-slate-500 shadow-sm">
            @if ($search !== '')
                「{{ $search }}」に一致するデータがありません。
            @else
                決済金管理のデータがありません。
            @endif
        </div>
    @else
        @include('admin.settlement-managements._cards')
    @endif
@endsection
