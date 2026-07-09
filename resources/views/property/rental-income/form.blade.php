@extends('layouts.admin')

@section('title', ($isEdit ? '月別家賃収入データ編集' : '月別家賃収入データ登録') . ' — ' . config('app.name'))

@section('content')
<div class="mb-6 flex items-center justify-between gap-4">
    <h2 class="text-2xl font-bold text-slate-900">{{ $isEdit ? '月別家賃収入データ編集' : '月別家賃収入データ登録' }}</h2>
    <a href="{{ route('property.rental-income.index', ['month' => $returnMonth ?? $activePaymentMonth ?? null]) }}" class="btn btn-ghost">一覧へ</a>
</div>

@if ($errors->any())
    <div class="alert alert-error mb-6">{{ $errors->first() }}</div>
@endif

@include('property.rental-income._form')
@endsection
