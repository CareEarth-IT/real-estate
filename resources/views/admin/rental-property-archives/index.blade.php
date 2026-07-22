@extends('layouts.admin')

@section('title', '賃貸物件保管 — ' . config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">賃貸物件保管</h2>
            <p class="mt-1 text-sm text-slate-500">
                表示件数: <strong class="text-slate-700">{{ $archives->count() }}</strong> 件
            </p>
        </div>
        @if ($canEdit ?? false)
            <form method="post" action="{{ route('admin.rental-property-archives.store') }}">
                @csrf
                <button type="submit" class="btn btn-primary">物件を追加</button>
            </form>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($archives->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-12 text-center text-slate-500 shadow-sm">
            賃貸物件がまだありません。「物件を追加」から登録してください。
        </div>
    @else
        @include('admin.rental-property-archives._cards')
    @endif
@endsection
