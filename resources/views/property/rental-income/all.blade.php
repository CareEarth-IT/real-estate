@extends('layouts.admin')

@section('title', '全家賃収入データ一覧 — ' . config('app.name'))

@php
    use App\Support\YearMonth;
@endphp

@section('content')
<div class="mb-6 flex flex-col gap-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">全家賃収入データ一覧</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ YearMonth::format($displayMonth) }} 基準の表示
                <span class="text-slate-400">（{{ $referenceDate->format('Y/m/d') }} 時点）</span>
                <span class="mx-1">·</span>
                契約者ブロック: <strong class="text-slate-700">{{ count($contractBlocks) }}</strong> 件
                @if ($search !== '')
                    <span class="text-slate-400">（「{{ $search }}」で絞り込み中）</span>
                @endif
                @if ($paymentStatus)
                    <span class="text-slate-400">（入金状況: {{ $paymentStatusLabels[$paymentStatus] ?? $paymentStatus }}）</span>
                @endif
            </p>
            <p class="mt-1 text-xs text-slate-400">
                今月の支払日分を表示します。未納・一時金・滞納の過去分がある場合はそちらを優先表示し、納金済の場合は次月分を表示します。
            </p>
        </div>
        <a href="{{ route('property.rental-income.index') }}" class="btn btn-outline">月別一覧へ</a>
    </div>

    @if ($upcomingPaymentCount > 0)
        <div class="rental-income-advance-notice" role="status">
            <p class="rental-income-advance-notice__title">支払日15日前のお知らせ</p>
            <p class="rental-income-advance-notice__body">
                支払日まで15日以内の契約者が
                <strong>{{ $upcomingPaymentCount }} 名</strong>
                います。該当分は次回支払日のデータを表示しています。
            </p>
        </div>
    @endif

    @include('property.rental-income._search-form')
</div>

@if ($contractBlocks === [])
    <div class="empty-state">
        <span class="empty-icon">📋</span>
        @if ($search !== '' || $paymentStatus)
            <h2>条件に一致するデータがありません</h2>
            <p>キーワードや入金状況を変えるか、クリアして再度お試しください。</p>
        @else
            <h2>データがありません</h2>
            <p>「月別家賃収入データ」から登録してください。</p>
            <a href="{{ route('property.rental-income.index') }}" class="btn btn-primary">月別家賃収入データへ</a>
        @endif
    </div>
@else
    @include('property.rental-income._blocks-grid', [
        'showAllDisplayHints' => ($paymentStatus ?? null) !== 'terminated',
        'showTerminatedDetails' => ($paymentStatus ?? null) === 'terminated',
    ])
@endif
@endsection
