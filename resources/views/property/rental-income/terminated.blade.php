@extends('layouts.admin')

@section('title', '解約データ — ' . config('app.name'))

@section('content')
<div class="mb-6 flex flex-col gap-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">解約データ</h2>
            <p class="mt-1 text-sm text-slate-500">
                解約済み契約: <strong class="text-slate-700">{{ count($contractBlocks) }}</strong> 件
                @if ($search !== '')
                    <span class="text-slate-400">（「{{ $search }}」で絞り込み中）</span>
                @endif
            </p>
            <p class="mt-1 text-xs text-slate-400">
                契約開始月〜解約月のデータを、入金状況に関係なく表示します。カードをクリックで期間内の一覧を確認できます。
            </p>
        </div>
        <a href="{{ route('property.rental-income.index') }}" class="btn btn-outline">月別一覧へ</a>
    </div>

    @include('property.rental-income._search-form', [
        'listRoute' => 'property.rental-income.terminated',
        'listParams' => $listParams,
        'hidePaymentStatus' => true,
    ])
</div>

@if ($contractBlocks === [])
    <div class="empty-state">
        <span class="empty-icon">📋</span>
        @if ($search !== '')
            <h2>条件に一致する解約データがありません</h2>
            <p>キーワードを変えるか、クリアして再度お試しください。</p>
        @else
            <h2>解約データがありません</h2>
            <p>契約詳細の未納行から「解約」を登録すると、ここに表示されます。</p>
            <a href="{{ route('property.rental-income.index') }}" class="btn btn-primary">月別家賃収入データへ</a>
        @endif
    </div>
@else
    @include('property.rental-income._blocks-grid', ['showTerminatedDetails' => true])
@endif
@endsection
