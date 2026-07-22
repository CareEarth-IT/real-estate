<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '賃貸-マスター管理 マスター管理')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/care-earth-home-logo.png') }}">
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
            --master-header-height: 72px;
        }
        .admin-header {
            position: sticky;
            top: 0;
            z-index: 50;
            flex-shrink: 0;
            background: var(--color-header-gradient);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        }
        .admin-nav-link {
            color: rgb(51 65 85);
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .admin-nav-link:hover:not(.is-active) {
            background-color: rgb(241 245 249);
        }
        .admin-nav-link.is-active {
            background-color: #5383c3;
            color: #fff;
        }
        .admin-table-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow: auto;
            overscroll-behavior: contain;
        }
        .admin-table-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background-color: rgb(241 245 249);
        }
        .admin-table-sticky {
            border-collapse: separate;
            border-spacing: 0;
        }
        .admin-table-sticky .sticky-col {
            position: sticky;
            left: 0;
            z-index: 2;
            background-color: #fff;
        }
        .admin-table-sticky thead .sticky-col {
            z-index: 4;
            background-color: rgb(241 245 249);
        }
        .admin-highlight-bg {
            background-color: #e0ffff;
        }

        .admin-table-sticky tbody td.master-check-cell.admin-highlight-bg,
        .admin-table-sticky tbody td.flow-check-cell.admin-highlight-bg,
        .admin-table-sticky tbody td.settlement-check-cell.admin-highlight-bg {
            background-color: #e0ffff !important;
        }

        .admin-table-sticky tbody tr.is-row-selected > td {
            background-color: #eff6ff;
        }

        .admin-table-sticky tbody tr.is-row-selected > td.sticky-col {
            background-color: #eff6ff !important;
        }
        .master-page {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
        }

        .master-table-panel {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
        }

        .master-table-card {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        .admin-layout-body {
            align-items: stretch;
            flex: 1;
            min-height: 0;
        }

        .master-sidebar {
            position: sticky;
            top: var(--master-header-height, 72px);
            z-index: 40;
            align-self: stretch;
            min-height: calc(100vh - var(--master-header-height, 72px));
        }

        .master-main-content {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            min-width: 0;
        }
    </style>
    @stack('head')
    @include('partials.app-url-helpers')
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex flex-col overflow-hidden">
    <header class="admin-header">
        <div class="flex items-center justify-between gap-4 px-6 py-3">
            <img
                src="{{ asset('images/care-earth-home-logo.png') }}"
                alt="Care Earth Home"
                class="h-12 w-auto rounded-md bg-white px-2.5 py-1.5 shadow-sm"
            >
            <a
                href="{{ route('admin.applications.index') }}"
                class="rounded-lg bg-white/90 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-white transition-colors"
            >
                業務管理画面へ
            </a>
        </div>
    </header>

    <div class="admin-layout-body flex flex-1 min-h-[calc(100vh-var(--master-header-height,72px))]">
        @include('master.partials.sidebar')

        <main class="master-main-content flex-1 p-8 overflow-hidden">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
