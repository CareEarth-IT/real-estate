@extends('layouts.admin-application-create')

@section('title', '申込登録 — ' . config('app.name'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.applications.index') }}" class="text-sm text-primary-600 hover:underline">← 申込一覧へ戻る</a>
        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">申込登録</h2>
        <p class="mt-1 text-sm text-slate-600"><span class="text-red-500">*</span> の付いた項目は必須です。</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
            <p class="font-medium">入力内容に誤りがあります。各項目をご確認ください。</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.applications.store') }}" class="space-y-6" novalidate>
        @csrf
        @include('admin.applications._form')
    </form>
@endsection

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
    @include('admin.applications._form-scripts')
@endpush
