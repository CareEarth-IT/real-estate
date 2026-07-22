@extends('layouts.admin')

@section('title', '物件データ — ' . config('app.name'))

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">物件データ</h2>
        <p class="mt-1 text-sm text-slate-500">
            登録件数: <strong class="text-slate-700">{{ $records->count() }}</strong> 件
        </p>
    </div>
    @if ($canEdit ?? false)
    <a href="{{ route('property.deal-drafts.create') }}" class="btn btn-primary shrink-0">+ 新規登録</a>
    @endif
</div>

@if (!empty($saved))
    <div class="alert alert-success mb-4">データを登録しました。</div>
@endif
@if (!empty($updated))
    <div class="alert alert-success mb-4">データを更新しました。</div>
@endif

@if ($records->isEmpty())
    <div class="empty-state">
        <span class="empty-icon">📋</span>
        <h2>データがありません</h2>
        <p>「新規登録」から物件データを追加してください。</p>
        @if ($canEdit ?? false)
        <a href="{{ route('property.deal-drafts.create') }}" class="btn btn-primary">データを登録する</a>
        @endif
    </div>
@else
    @include('property.deal-drafts._spreadsheet-table')
@endif
@endsection

@if ($canEdit ?? false)
@push('scripts')
    @include('property.deal-drafts._inline-scripts')
@endpush
@endif
