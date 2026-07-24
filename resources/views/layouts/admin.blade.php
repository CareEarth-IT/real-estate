<!DOCTYPE html>
@php
    $adminPageLoaderEnabled = true;
    $isUsersPage = request()->routeIs('users.*');
    $isRentalPropertyArchivesPage = request()->routeIs('admin.rental-property-archives.*');
    $isRentalAdminPage = request()->routeIs(
        'home',
        'admin.applications.*',
        'admin.flow-managements.*',
        'admin.settlement-managements.*',
    );
    $isPortalPage = request()->routeIs(
        'properties.*',
        'reference.*',
        'users.*',
        'property.rental-income.*',
        'property.deal-drafts.*',
    );
    // ユーザー管理ではサイドバーを出さない
    $showPortalSidebar = $isPortalPage && ! $isUsersPage;

    $showPortalMenu = session('authenticated');
@endphp
<html lang="ja" @class(['admin-loading' => $adminPageLoaderEnabled])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Care Earth Home')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/care-earth-home-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/care-earth-home-logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/portal-menu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal-master.css') }}?v=20260724-input-border">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('head')
    <style>
        @if ($adminPageLoaderEnabled)
        html.admin-loading { overflow: hidden; }
        html.admin-skip-loader { overflow: auto; }
        html.admin-skip-loader .admin-page-loader { display: none; }
        .admin-page-loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            transition: opacity 0.45s ease, visibility 0.45s ease;
        }
        .admin-page-loader.is-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        .admin-page-loader-inner {
            display: flex;
            align-items: center;
            justify-content: center;
            width: min(300px, 72vw);
        }
        .admin-page-loader-logo-wrap {
            width: 100%;
        }
        .admin-page-loader-logo-img {
            display: block;
            width: 100%;
            height: auto;
            opacity: 0;
            animation: adminLoaderLogoFadeIn 0.75s ease-out forwards;
        }
        @keyframes adminLoaderLogoFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .admin-page-loader-logo-img {
                opacity: 1;
                animation: none;
                transform: none;
            }
        }
        @endif
    </style>
    @if ($adminPageLoaderEnabled)
    <script>
        (function () {
            if (sessionStorage.getItem('admin-skip-loader') === '1') {
                document.documentElement.classList.add('admin-skip-loader');
                document.documentElement.classList.remove('admin-loading');
            }
        })();
    </script>
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
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
        .resizable-table { table-layout: fixed; width: 100%; }
        .resizable-table th { position: relative; overflow: hidden; }
        .resizable-table td { overflow: hidden; }
        .col-resize-handle {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 6px;
            cursor: col-resize;
            user-select: none;
            touch-action: none;
        }
        .col-resize-handle:hover,
        .col-resize-handle.active {
            background: rgba(37, 99, 235, 0.25);
        }
        body.col-resizing { cursor: col-resize; user-select: none; }

        .admin-fixed-table {
            table-layout: fixed;
            width: max-content;
            min-width: 100%;
        }

        .admin-fixed-table th,
        .admin-fixed-table td {
            overflow: hidden;
        }

        .admin-table-sticky {
            border-collapse: separate;
            border-spacing: 0;
        }
        .admin-table-sticky .sticky-col {
            position: sticky;
            z-index: 2;
        }
        .admin-table-sticky thead .sticky-col {
            z-index: 4;
            background-color: rgb(241 245 249);
        }
        .admin-table-sticky tbody .sticky-col {
            background-color: #fff;
        }
        .admin-table-sticky .sticky-col-last {
            box-shadow: 4px 0 6px -2px rgba(15, 23, 42, 0.12);
        }

        tr.text-white a.admin-checked-link {
            color: #fff;
        }

        :root {
            --admin-highlight-bg: #e0ffff;
            --admin-row-selected-bg: #eff6ff;
            --admin-row-selected-border: #5383c3;
        }

        .admin-table-sticky tbody tr {
            cursor: pointer;
        }

        .admin-table-sticky tbody tr.is-row-selected > td {
            background-color: var(--admin-row-selected-bg);
        }

        .admin-table-sticky tbody tr.is-row-selected > td:first-child {
            box-shadow: inset 3px 0 0 var(--admin-row-selected-border);
        }

        .admin-table-sticky tbody tr input,
        .admin-table-sticky tbody tr textarea,
        .admin-table-sticky tbody tr select,
        .admin-table-sticky tbody tr button,
        .admin-table-sticky tbody tr a {
            cursor: auto;
        }

        .admin-highlight-bg {
            background-color: var(--admin-highlight-bg);
            color: rgb(38 38 38);
        }

        .admin-table-sticky tbody td.master-check-cell.admin-highlight-bg,
        .admin-table-sticky tbody td.flow-check-cell.admin-highlight-bg,
        .admin-table-sticky tbody td.settlement-check-cell.admin-highlight-bg {
            background-color: var(--admin-highlight-bg) !important;
            color: rgb(38 38 38);
        }

        .admin-table-sticky tbody tr.is-row-selected > td.sticky-col {
            background-color: var(--admin-row-selected-bg) !important;
            color: rgb(38 38 38);
        }

        .flow-section-disabled {
            background-color: rgb(241 245 249);
            color: rgb(148 163 184);
        }

        .flow-section-disabled input,
        .flow-section-disabled textarea {
            background-color: rgb(226 232 240);
            cursor: not-allowed;
        }

        .admin-main-content {
            opacity: 0;
            transform: translateY(10px);
        }

        .admin-main-content.is-visible {
            animation: adminFadeIn 0.4s ease-out forwards;
        }

        .admin-main-content.is-visible-instant {
            opacity: 1;
            transform: translateY(0);
        }

        .admin-main-content.is-leaving {
            animation: adminFadeOut 0.25s ease-in forwards;
        }

        @keyframes adminFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes adminFadeOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-6px);
            }
        }

        .admin-nav-link {
            color: rgb(51 65 85);
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.15s ease;
        }

        .admin-nav-link:hover:not(.is-active) {
            background-color: rgb(241 245 249);
            transform: translateX(2px);
        }

        .admin-nav-link.is-active {
            background-color: #5383c3;
            color: #fff;
            box-shadow: 0 1px 2px 0 rgba(15, 23, 42, 0.05);
        }

        .admin-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: var(--color-header-gradient);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        }

        .admin-header--portal {
            background: var(--color-portal-header-gradient);
        }

        .admin-header--portal .header-action-link:hover,
        .admin-header--portal button[type="submit"].header-action-btn:hover {
            color: #c2410c;
        }

        .admin-header .header-action-link,
        .admin-header button[type="submit"].header-action-btn {
            border: 2px solid rgba(255, 255, 255, 0.7);
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .admin-header .header-action-link:hover,
        .admin-header button[type="submit"].header-action-btn:hover {
            background: #fff;
            color: #5383c3;
            border-color: #fff;
        }

        .admin-layout-body {
            align-items: stretch;
            flex: 1;
        }

        .admin-sidebar {
            position: sticky;
            top: var(--admin-header-height, 72px);
            z-index: 40;
            align-self: stretch;
            min-height: calc(100vh - var(--admin-header-height, 72px));
        }

        .admin-table-scroll {
            max-height: calc(100vh - var(--admin-header-height, 72px) - 11rem);
            overflow: auto;
            overscroll-behavior: contain;
        }

        .admin-table-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background-color: rgb(241 245 249);
        }

        .admin-table-scroll .admin-table-sticky thead .sticky-col {
            z-index: 5;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">
    @if ($adminPageLoaderEnabled)
    <div id="admin-page-loader" class="admin-page-loader" aria-live="polite" aria-busy="true" aria-label="読み込み中">
        <div class="admin-page-loader-inner">
            <x-admin-loader-logo />
        </div>
    </div>
    @endif

    <header @class([
        'admin-header',
        'admin-header--portal' => $isPortalPage,
    ])>
        <div class="flex items-center justify-between gap-4 px-6 py-3">
            <a href="{{ route('home') }}">
                <img
                    src="{{ asset('images/care-earth-home-logo.png') }}"
                    alt="Care Earth Home"
                    class="h-12 w-auto rounded-md bg-white px-2.5 py-1.5 shadow-sm"
                >
            </a>
            <div class="flex items-center gap-3 flex-wrap justify-end">
                @if ($showPortalMenu)
                    <x-portal-menu variant="admin" />
                @endif
            </div>
        </div>
    </header>

    <div class="admin-layout-body flex flex-1 min-h-[calc(100vh-var(--admin-header-height,72px))]">
        @if ($showPortalSidebar ?? false)
            <x-portal-sidebar />
        @elseif ($isRentalAdminPage)
            <x-rental-sidebar />
        @endif

        <main id="admin-main-content" @class([
            'admin-main-content flex-1 p-8 overflow-x-auto',
            $adminPageLoaderEnabled ? '' : 'is-visible',
            'portal-master-content' => ($isPortalPage || $isRentalAdminPage || $isRentalPropertyArchivesPage || ($isUsersPage ?? false)),
        ])>
            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>

    <div id="admin-uncheck-confirm" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="admin-uncheck-confirm-message">
            <p id="admin-uncheck-confirm-message" class="text-base text-slate-800">本当にチェックを外しますか？</p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="admin-uncheck-confirm-yes" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                    はい
                </button>
                <button type="button" id="admin-uncheck-confirm-no" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    いいえ
                </button>
            </div>
        </div>
    </div>

    <script>
        window.confirmUncheckTransition = (message = '本当にチェックを外しますか？') => {
            return new Promise((resolve) => {
                const overlay = document.getElementById('admin-uncheck-confirm');
                const messageEl = document.getElementById('admin-uncheck-confirm-message');
                const yesButton = document.getElementById('admin-uncheck-confirm-yes');
                const noButton = document.getElementById('admin-uncheck-confirm-no');

                if (!overlay || !messageEl || !yesButton || !noButton) {
                    resolve(window.confirm(message));
                    return;
                }

                messageEl.textContent = message;
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');

                const cleanup = (result) => {
                    overlay.classList.add('hidden');
                    overlay.classList.remove('flex');
                    yesButton.removeEventListener('click', onYes);
                    noButton.removeEventListener('click', onNo);
                    resolve(result);
                };

                const onYes = () => cleanup(true);
                const onNo = () => cleanup(false);

                yesButton.addEventListener('click', onYes);
                noButton.addEventListener('click', onNo);
            });
        };
    </script>

    @include('partials.app-url-helpers')

    @stack('scripts')
    <script>
        function syncStickyCellBackgrounds(table) {
            const selectedBg = getComputedStyle(document.documentElement).getPropertyValue('--admin-row-selected-bg').trim();

            table.querySelectorAll('tbody tr').forEach((row) => {
                const isSelected = row.classList.contains('is-row-selected');

                row.querySelectorAll('.sticky-col').forEach((cell) => {
                    if (isSelected) {
                        cell.style.backgroundColor = selectedBg;
                        cell.style.color = 'rgb(38 38 38)';
                    } else if (cell.classList.contains('admin-highlight-bg')) {
                        const highlightBg = getComputedStyle(document.documentElement).getPropertyValue('--admin-highlight-bg').trim() || '#e0ffff';
                        cell.style.backgroundColor = highlightBg;
                        cell.style.color = 'rgb(38 38 38)';
                    } else {
                        cell.style.backgroundColor = '#fff';
                        cell.style.color = '';
                    }
                });
            });
        }

        function updateStickyColumnOffsets(table) {
            const stickyCount = parseInt(table.dataset.stickyCols || '3', 10);
            const headerRow = table.querySelector('thead tr');
            if (!headerRow) {
                return;
            }

            const headerCells = Array.from(headerRow.children).slice(0, stickyCount);
            let left = 0;

            headerCells.forEach((headerCell, index) => {
                const width = headerCell.getBoundingClientRect().width;
                table.querySelectorAll(`tr > :nth-child(${index + 1})`).forEach((cell) => {
                    if (cell.classList.contains('sticky-col')) {
                        cell.style.left = `${left}px`;
                    }
                });
                left += width;
            });

            syncStickyCellBackgrounds(table);
        }

        window.refreshAdminStickyColumns = () => {
            document.querySelectorAll('.admin-table-sticky').forEach((table) => {
                updateStickyColumnOffsets(table);
            });
        };

        function initAdminRowSelection() {
            document.querySelectorAll('.admin-table-sticky tbody').forEach((tbody) => {
                tbody.addEventListener('click', (event) => {
                    if (event.target.closest('input, textarea, select, button, a, label')) {
                        return;
                    }

                    const row = event.target.closest('tr');
                    if (!row || row.parentElement !== tbody) {
                        return;
                    }

                    const wasSelected = row.classList.contains('is-row-selected');
                    tbody.querySelectorAll('tr.is-row-selected').forEach((selectedRow) => {
                        selectedRow.classList.remove('is-row-selected');
                    });

                    if (!wasSelected) {
                        row.classList.add('is-row-selected');
                    }

                    const table = tbody.closest('table');
                    if (table) {
                        syncStickyCellBackgrounds(table);
                    }

                    document.dispatchEvent(new CustomEvent('admin-row-selection-changed', {
                        detail: { row: tbody.querySelector('tr.is-row-selected') },
                    }));
                });
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.admin-table-sticky').forEach((table) => {
                updateStickyColumnOffsets(table);
            });

            initAdminRowSelection();

            window.addEventListener('resize', window.refreshAdminStickyColumns);
        });

        @if ($adminPageLoaderEnabled)
        document.addEventListener('DOMContentLoaded', () => {
            const loader = document.getElementById('admin-page-loader');
            const mainContent = document.getElementById('admin-main-content');
            const skipLoader = sessionStorage.getItem('admin-skip-loader') === '1';

            if (skipLoader) {
                sessionStorage.removeItem('admin-skip-loader');
                document.documentElement.classList.remove('admin-skip-loader');
                loader?.remove();
                mainContent?.classList.add('is-visible');
                document.documentElement.classList.remove('admin-loading');
                return;
            }

            const loaderStartedAt = performance.now();
            const minLoaderDuration = 800;

            const hidePageLoader = () => {
                if (!loader) {
                    mainContent?.classList.add('is-visible-instant');
                    document.documentElement.classList.remove('admin-loading');
                    return;
                }

                const elapsed = performance.now() - loaderStartedAt;
                const remaining = Math.max(0, minLoaderDuration - elapsed);

                window.setTimeout(() => {
                    loader.classList.add('is-hidden');
                    loader.setAttribute('aria-busy', 'false');
                    mainContent?.classList.add('is-visible-instant');
                    document.documentElement.classList.remove('admin-loading');

                    loader.addEventListener('transitionend', () => {
                        loader.remove();
                    }, { once: true });
                }, remaining);
            };

            if (document.readyState === 'complete') {
                hidePageLoader();
            } else {
                window.addEventListener('load', hidePageLoader, { once: true });
            }

            window.setTimeout(hidePageLoader, 5000);
        });
        @endif

        document.addEventListener('DOMContentLoaded', () => {
            const navLinks = document.querySelectorAll('.admin-nav-link');

            navLinks.forEach((link) => {
                link.addEventListener('click', () => {
                    sessionStorage.setItem('admin-skip-loader', '1');
                });
            });

            document.querySelectorAll('.admin-pagination-link').forEach((link) => {
                link.addEventListener('click', () => {
                    sessionStorage.setItem('admin-skip-loader', '1');
                });
            });

            document.querySelectorAll('.admin-search-clear-link').forEach((link) => {
                link.addEventListener('click', () => {
                    sessionStorage.setItem('admin-skip-loader', '1');
                });
            });

            document.querySelectorAll('.admin-search-form').forEach((form) => {
                form.addEventListener('submit', () => {
                    sessionStorage.setItem('admin-skip-loader', '1');
                });
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.resizable-table').forEach((table) => {
                const tableId = table.dataset.tableId || 'admin-table';
                const storageKey = `admin-table-widths-${tableId}`;
                const headers = Array.from(table.querySelectorAll('thead th'));

                const applyWidth = (index, widthPx) => {
                    const width = `${Math.max(60, widthPx)}px`;
                    if (headers[index]) {
                        headers[index].style.width = width;
                    }
                    table.querySelectorAll('tbody tr').forEach((row) => {
                        const cell = row.children[index];
                        if (cell) {
                            cell.style.width = width;
                        }
                    });
                };

                const saveWidths = () => {
                    const widths = headers.map((th) => th.style.width || `${th.offsetWidth}px`);
                    localStorage.setItem(storageKey, JSON.stringify(widths));
                };

                const saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
                headers.forEach((th, index) => {
                    if (saved[index]) {
                        applyWidth(index, parseInt(saved[index], 10));
                    }

                    const handle = document.createElement('div');
                    handle.className = 'col-resize-handle';
                    handle.title = 'ドラッグして列幅を調整';
                    th.appendChild(handle);

                    handle.addEventListener('mousedown', (event) => {
                        event.preventDefault();
                        const startX = event.pageX;
                        const startWidth = th.offsetWidth;

                        handle.classList.add('active');
                        document.body.classList.add('col-resizing');

                        const onMouseMove = (moveEvent) => {
                            applyWidth(index, startWidth + moveEvent.pageX - startX);
                        };

                        const onMouseUp = () => {
                            handle.classList.remove('active');
                            document.body.classList.remove('col-resizing');
                            document.removeEventListener('mousemove', onMouseMove);
                            document.removeEventListener('mouseup', onMouseUp);
                            saveWidths();
                            if (typeof window.refreshAdminStickyColumns === 'function') {
                                window.refreshAdminStickyColumns();
                            }
                        };

                        document.addEventListener('mousemove', onMouseMove);
                        document.addEventListener('mouseup', onMouseUp);
                    });
                });

                if (typeof window.refreshAdminStickyColumns === 'function') {
                    window.refreshAdminStickyColumns();
                }
            });
        });
    </script>
</body>
</html>
