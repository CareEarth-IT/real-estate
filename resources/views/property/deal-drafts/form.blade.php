@extends('layouts.admin')

@section('title', ($isEdit ? '物件データ編集' : '物件データ登録') . ' — ' . config('app.name'))

@section('content')
<div class="mb-6 flex items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">{{ $isEdit ? '物件データ編集' : '物件データ登録' }}</h2>
        @if ($isEdit)
            <p class="mt-1 text-sm text-slate-500">案件番号: <strong>{{ $record->case_number }}</strong></p>
        @endif
    </div>
    <a href="{{ route('property.deal-drafts.index') }}" class="btn btn-ghost shrink-0">一覧へ</a>
</div>

@if ($errors->any())
    <div class="alert alert-error mb-6">{{ $errors->first() }}</div>
@endif

@include('property.deal-drafts._form')
@endsection
