@extends('layouts.rental')

@section('title', config('app.name'))

@section('content')
    {{-- ログイン画面は停止。showLogin はポータル誘導または auth.unavailable を表示する。 --}}
@endsection
