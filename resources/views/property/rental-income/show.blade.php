@extends('layouts.admin')

@section('title', '契約詳細 — ' . config('app.name'))

@section('content')
<div class="mb-6">
    <a
        href="{{ $termination
            ? route('property.rental-income.terminated')
            : route('property.rental-income.index', ['month' => $returnMonth]) }}"
        class="inline-flex items-center text-sm text-primary-600 hover:underline mb-4"
    >
        @if ($termination)
            ← 解約データへ戻る
        @else
            ← {{ \App\Support\YearMonth::formatShort($returnMonth) }} の一覧へ戻る
        @endif
    </a>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
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
                    @if ($termination)
                        解約
                        <strong class="text-slate-700">{{ ($termination->terminated_on ?? $termination->terminated_at)?->format('Y/m/d') ?? $contractEndOn?->format('Y/m/d') }}</strong>
                    @else
                        契約満了
                        <strong class="text-slate-700">{{ $contractEndOn->format('Y/m/d') }}</strong>
                    @endif
                </p>
                @if ($termination && $records->isNotEmpty())
                    <p class="mt-1 text-xs text-slate-400">
                        表示範囲: 契約開始月〜解約月（{{ $records->count() }} 件 / 入金状況不問）
                    </p>
                @endif
            @endif
        </div>
    </div>

    @if ($termination)
        <div class="rental-income-termination-card mt-4">
            <p class="rental-income-termination-card__title">解約済み</p>
            <dl class="rental-income-termination-card__grid">
                <div>
                    <dt>退去区分</dt>
                    <dd>{{ $moveOutTypeLabels[$termination->move_out_type] ?? $termination->move_out_type }}</dd>
                </div>
                <div>
                    <dt>退去費</dt>
                    <dd>{{ $termination->move_out_cost !== null ? number_format($termination->move_out_cost).' 円' : '—' }}</dd>
                </div>
                <div>
                    <dt>解約日</dt>
                    <dd>{{ $termination->terminated_on?->format('Y/m/d') ?? ($termination->terminated_at?->format('Y/m/d') ?? '—') }}</dd>
                </div>
                <div class="rental-income-termination-card__reason">
                    <dt>退去理由</dt>
                    <dd>{{ $termination->move_out_reason ?: '—' }}</dd>
                </div>
            </dl>
        </div>
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

@if (($canEdit ?? false) && $termination === null)
    @include('property.rental-income._terminate-modal')
@endif

@if ($canEdit ?? false)
    @push('scripts')
        @include('property.rental-income._inline-scripts')
        @if ($termination === null)
            @include('property.rental-income._terminate-scripts')
        @endif
    @endpush
@endif
@endsection
