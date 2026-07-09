<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '申込登録 — ' . config('app.name'))</title>
    <link rel="icon" type="image/png" href="{{ asset('images/care-earth-home-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/care-earth-home-logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8' },
                    },
                },
            },
        };
    </script>
    <style>
        :root {
            --application-create-bg: #eef8fc;
            --application-create-bg-deep: #d4edf7;
        }

        body.application-create-page {
            min-height: 100vh;
            background: linear-gradient(160deg, var(--application-create-bg) 0%, var(--application-create-bg-deep) 100%);
            color: rgb(30 41 59);
        }

        .flatpickr-calendar {
            border-radius: 0.75rem;
            border-color: rgb(226 232 240);
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15);
        }
    </style>
    @stack('head')
</head>
<body class="application-create-page">
    <main class="mx-auto min-h-screen w-full max-w-4xl px-4 py-8 sm:px-6 sm:py-10">
        @if (session('success'))
            <div class="mb-6 rounded-lg border border-white/40 bg-white/90 px-4 py-3 text-sm text-slate-800 shadow-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.app-url-helpers')
    @stack('scripts')
</body>
</html>
