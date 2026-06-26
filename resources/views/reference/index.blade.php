@extends('layouts.admin')

@section('title', '参照一覧 — ' . config('app.name'))

@php
    use App\Support\Format;
@endphp

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-900">参照一覧</h2>
    <p class="mt-1 text-sm text-slate-500">マスターデータを参照する簡易一覧（{{ count($properties) }} 件）</p>
</div>

<nav class="view-sub-tabs mode-tabs" aria-label="参照タブ">
    <a href="{{ route('reference.index') }}"
       class="mode-tab {{ $tab === 'list' ? 'active' : '' }}">一覧</a>
    <a href="{{ route('reference.index', ['tab' => 'documents']) }}"
       class="mode-tab {{ $tab === 'documents' ? 'active' : '' }}">書類閲覧</a>
</nav>

@if(empty($properties) || count($properties) === 0)
<div class="empty-state">
    <span class="empty-icon">📋</span>
    <h2>データがありません</h2>
    <p>マスターデータを登録すると、ここに表示されます。</p>
    <a href="{{ route('properties.create') }}" class="btn btn-primary">データを登録する</a>
</div>

@elseif($tab === 'list')

<div class="table-wrapper">
    <table class="data-table reference-table">
        <thead>
            <tr>
                <th>作成日時</th>
                <th>担当営業</th>
                <th>購入者</th>
                <th>仲介業者名</th>
                <th>オーナー名</th>
                <th>物件住所</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($properties as $p)
            <tr>
                <td class="date-cell">{{ Format::formatDateTime($p->created_at) }}</td>
                <td>{{ $p->sales_person ?: '—' }}</td>
                <td>{{ $p->buyer_name }}</td>
                <td>{{ $p->broker_name ?: '—' }}</td>
                <td>{{ $p->owner_name ?: '—' }}</td>
                <td class="address-cell">{{ $p->property_address }}</td>
                <td class="actions-cell">
                    <a href="{{ route('properties.show', ['property' => $p, 'from' => 'reference']) }}" class="btn btn-outline btn-sm">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@else

<div class="reference-documents-list">
    @foreach($properties as $p)
    @php
        $docCount = $propertyService->countDocuments($p);
        $property = $p;
    @endphp
    <section class="detail-section reference-doc-block">
        <div class="reference-doc-header">
            <div>
                <h2 class="reference-doc-title">{{ $p->buyer_name }}</h2>
                <p class="reference-doc-meta">
                    {{ Format::formatDateTime($p->created_at) }}
                    <span class="subtitle-sep">|</span>
                    担当: {{ $p->sales_person ?: '—' }}
                    <span class="subtitle-sep">|</span>
                    {{ $p->property_address }}
                </p>
            </div>
            <span class="doc-count-badge">{{ $docCount }} 件</span>
        </div>
        @if($docCount > 0)
            @include('components.property-documents')
        @else
            <p class="doc-none">書類は未登録です</p>
        @endif
    </section>
    @endforeach
</div>

@endif
@endsection
