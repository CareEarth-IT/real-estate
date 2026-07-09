@extends('layouts.admin')

@section('title', '月別家賃収入データ — ' . config('app.name'))

@php
    use App\Support\YearMonth;
@endphp

@section('content')
<div class="mb-6 flex flex-col gap-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">月別家賃収入データ</h2>
            <p class="mt-1 text-sm text-slate-500">
                契約者ブロック: <strong class="text-slate-700">{{ count($contractBlocks) }}</strong> 件
                @if ($search !== '')
                    <span class="text-slate-400">（「{{ $search }}」で絞り込み中）</span>
                @endif
                @if ($paymentStatus)
                    <span class="text-slate-400">（入金状況: {{ $paymentStatusLabels[$paymentStatus] ?? $paymentStatus }}）</span>
                @endif
            </p>
        </div>
        <div class="flex items-end gap-3 flex-wrap">
            @include('property.rental-income._month-picker', [
                'paymentMonthOptions' => $paymentMonthOptions,
                'activePaymentMonth' => $activePaymentMonth,
            ])
            @if ($canEdit ?? false)
            <a href="{{ route('property.rental-income.create', ['month' => $activePaymentMonth]) }}" class="btn btn-primary">+ 新規登録</a>
            @endif
        </div>
    </div>

    @include('property.rental-income._search-form')
</div>

@if ($contractBlocks === [])
    <div class="empty-state">
        <span class="empty-icon">📋</span>
        @if ($search !== '' || $paymentStatus)
            <h2>条件に一致するデータがありません</h2>
            <p>キーワードや入金状況を変えるか、クリアして再度お試しください。</p>
        @else
            <h2>{{ YearMonth::formatShort($activePaymentMonth) }} のデータがありません</h2>
            <p>「新規登録」から月別家賃収入データを追加してください。</p>
            @if ($canEdit ?? false)
            <a href="{{ route('property.rental-income.create', ['month' => $activePaymentMonth]) }}" class="btn btn-primary">データを登録する</a>
            @endif
        @endif
    </div>
@else
    @include('property.rental-income._blocks-grid')
@endif
@endsection

@push('scripts')
    @include('property.rental-income._inline-scripts', [
        'rentalIncomeIndexUrl' => route('property.rental-income.index'),
        'listQuery' => array_filter([
            'search' => $search ?: null,
            'payment_status' => $paymentStatus,
        ]),
    ])
@endpush
