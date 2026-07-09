@extends('layouts.admin')

@section('title', '契約詳細 — ' . config('app.name'))

@section('content')
<div class="mb-6">
    <a
        href="{{ route('property.rental-income.index', ['month' => $returnMonth]) }}"
        class="inline-flex items-center text-sm text-primary-600 hover:underline mb-4"
    >
        ← {{ \App\Support\YearMonth::formatShort($returnMonth) }} の一覧へ戻る
    </a>

    <h2 class="text-2xl font-bold text-slate-900">契約詳細</h2>
    <p class="mt-2 text-sm text-slate-600">
        <span class="font-semibold text-slate-900">{{ $representative->contractor ?: '（契約者未設定）' }}</span>
        @if ($representative->property_name)
            <span class="text-slate-400 mx-1">|</span>
            {{ $representative->property_name }}
        @endif
    </p>
    @if ($contractPeriodLabel)
        <p class="mt-1 text-sm text-slate-500">
            契約開始
            <strong class="text-slate-700">{{ $contractStartOn->format('Y/m/d') }}</strong>
            <span class="text-slate-400 mx-1">～</span>
            契約満了
            <strong class="text-slate-700">{{ $contractEndOn->format('Y/m/d') }}</strong>
        </p>
    @endif
</div>

@if ($records->isEmpty())
    <div class="empty-state">
        <span class="empty-icon">📋</span>
        <h2>データがありません</h2>
    </div>
@else
    @include('property.rental-income._contract-detail-layout')
@endif

@if ($canEdit ?? false)
    @push('scripts')
        @include('property.rental-income._inline-scripts')
    @endpush
@endif
@endsection
