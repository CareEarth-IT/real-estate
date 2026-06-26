@extends('layouts.admin')

@section('title', 'データ登録 — ' . config('app.name'))

@section('content')
<div class="mb-6 flex items-center justify-between gap-4">
    <h2 class="text-2xl font-bold text-slate-900">データ登録</h2>
    <a href="{{ route('properties.index') }}" class="btn btn-ghost">一覧へ</a>
</div>

@if($errors->any())
<div class="alert alert-error">{{ $errors->first() }}</div>
@endif

@include('components.property-form')
@endsection
