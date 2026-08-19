@extends('layouts.rental')

@section('title', config('app.name'))

@section('content')
    {{-- ログイン画面は停止。社員ポータル SSO のみ利用する。 --}}
@endsection
