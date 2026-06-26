@extends('layouts.admin')

@section('title', '物件 #' . $property->id . ' — ' . config('app.name'))

@php
    use App\Support\Format;
    use App\Support\Role;
    $backUrl = $fromReference ? route('reference.index') : route('properties.index');
    $backLabel = $fromReference ? '参照一覧に戻る' : '一覧に戻る';
    $showAccountingPrices = $showAccountingPrices ?? true;
@endphp

@section('content')
@if(!empty($saved))
<div class="alert alert-success">データを登録しました。</div>
@endif
@if(!empty($updated))
<div class="alert alert-success">データを更新しました。</div>
@endif

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <a href="{{ $backUrl }}" class="back-link">← {{ $backLabel }}</a>
        <h2 class="text-2xl font-bold text-slate-900">物件 #{{ $property->id }}</h2>
        @if(!$isEdit)
        <p class="mt-1 text-sm text-slate-500">
            作成日時: {{ Format::formatDateTime($property->created_at) }}
            @if($property->sales_person)
            <span class="subtitle-sep">|</span> 担当営業: {{ $property->sales_person }}
            @endif
        </p>
        @endif
    </div>

    <div class="mode-tabs">
        <a href="{{ route('properties.show', array_filter(['property' => $property, 'from' => $fromReference ? 'reference' : null])) }}"
           class="mode-tab {{ !$isEdit ? 'active' : '' }}">閲覧</a>
        <a href="{{ route('properties.edit', array_filter(['property' => $property, 'from' => $fromReference ? 'reference' : null])) }}"
           class="mode-tab {{ $isEdit ? 'active' : '' }}">編集</a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-error">{{ $errors->first() }}</div>
@endif

@if($isEdit)

@include('components.property-form')

@else

<div class="detail-grid{{ $showAccountingPrices ? '' : ' detail-grid-single' }}">
    <section class="detail-section">
        <h2 class="section-title">基本情報</h2>
        <dl class="detail-list">
            <div class="detail-row">
                <dt>購入者</dt>
                <dd>{{ $property->buyer_name }}</dd>
            </div>
            <div class="detail-row">
                <dt>仲介業者名</dt>
                <dd>{{ $property->broker_name ?: '—' }}</dd>
            </div>
            <div class="detail-row">
                <dt>オーナー名</dt>
                <dd>{{ $property->owner_name ?: '—' }}</dd>
            </div>
            <div class="detail-row">
                <dt>物件住所</dt>
                <dd class="address-highlight">{{ $property->property_address }}</dd>
            </div>
        </dl>
    </section>

    @if($showAccountingPrices)
    <section class="detail-section">
        <h2 class="section-title">価格・費用</h2>
        <dl class="detail-list">
            <div class="detail-row">
                <dt>建物取得価格</dt>
                <dd class="price">{{ Format::formatYen((int) $property->building_price) }}</dd>
            </div>
            <div class="detail-row">
                <dt>土地取得価格</dt>
                <dd class="price">{{ Format::formatYen((int) $property->land_price) }}</dd>
            </div>
            <div class="detail-row highlight-row">
                <dt>物件価格（合算）</dt>
                <dd class="price total">{{ Format::formatYen((int) $property->total_price) }}</dd>
            </div>
            <div class="detail-row">
                <dt>登記費用</dt>
                <dd class="price">{{ Format::formatYen((int) $property->registration_fee) }}</dd>
            </div>
            <div class="detail-row">
                <dt>仲介手数料</dt>
                <dd class="price">{{ Format::formatYen((int) $property->brokerage_fee) }}</dd>
            </div>
            <div class="detail-row">
                <dt>固定資産税</dt>
                <dd class="price">{{ Format::formatYen((int) $property->property_tax) }}</dd>
            </div>
        </dl>
    </section>
    @endif
</div>

<section class="detail-section documents-section">
    <h2 class="section-title">添付書類</h2>
    @include('components.property-documents')
</section>

@if($fromReference)
<p class="reference-back-hint">
    <a href="{{ route('reference.index', ['tab' => 'documents']) }}" class="table-link">参照一覧の書類タブ</a>からも閲覧できます。
</p>
@endif

@endif
@endsection
