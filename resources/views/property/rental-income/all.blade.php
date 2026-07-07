@extends('layouts.admin')

@section('title', '全家賃収入データ一覧 — ' . config('app.name'))

@section('content')
<div class="mb-6 flex flex-col gap-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">全家賃収入データ一覧</h2>
            <p class="mt-1 text-sm text-slate-500">
                全期間の登録件数: <strong class="text-slate-700">{{ $records->count() }}</strong> 件
                @if ($search !== '')
                    <span class="text-slate-400">（「{{ $search }}」で絞り込み中）</span>
                @endif
                @if ($paymentStatus)
                    <span class="text-slate-400">（入金状況: {{ $paymentStatusLabels[$paymentStatus] ?? $paymentStatus }}）</span>
                @endif
            </p>
        </div>
        <a href="{{ route('property.rental-income.index') }}" class="btn btn-outline">月別一覧へ</a>
    </div>

    @include('property.rental-income._search-form')
</div>

@if ($records->isEmpty())
    <div class="empty-state">
        <span class="empty-icon">📋</span>
        @if ($search !== '' || $paymentStatus)
            <h2>条件に一致するデータがありません</h2>
            <p>キーワードや入金状況を変えるか、クリアして再度お試しください。</p>
        @else
            <h2>データがありません</h2>
            <p>月別の「家賃収入データ」から登録してください。</p>
            <a href="{{ route('property.rental-income.index') }}" class="btn btn-primary">家賃収入データへ</a>
        @endif
    </div>
@else
    @include('property.rental-income._records-table', ['showPaymentMonthColumn' => true])
@endif
@endsection

@push('scripts')
    @include('property.rental-income._inline-scripts')
@endpush
