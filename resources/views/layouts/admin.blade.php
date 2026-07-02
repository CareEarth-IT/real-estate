<!DOCTYPE html>
@php($adminPageLoaderEnabled = false)
<html lang="ja" @class(['admin-loading' => $adminPageLoaderEnabled])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '管理画面')</title>
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
        }
        .admin-page-loader-animation-wrap {
            width: min(140px, 36vw);
            aspect-ratio: 55 / 50;
        }
        .admin-page-loader-animation-svg {
            display: block;
            width: 100%;
            height: 100%;
            overflow: visible;
            pointer-events: none;
        }
        .admin-loader-path-track {
            fill: none;
            stroke: #4680bd;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
            opacity: 0.18;
        }
        .admin-loader-path-active {
            fill: none;
            stroke: #4680bd;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-dasharray: var(--loader-path-length, 240);
            stroke-dashoffset: var(--loader-path-length, 240);
            transition: stroke-dashoffset 0.22s ease-out;
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

        .admin-main-content {
            opacity: 0;
            transform: translateY(10px);
        }

        .admin-main-content.is-visible {
            animation: adminFadeIn 0.4s ease-out forwards;
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
            background: linear-gradient(to right, #7599BC 0%, #3E6492 100%);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    @if ($adminPageLoaderEnabled)
    <div id="admin-page-loader" class="admin-page-loader" aria-live="polite" aria-busy="true">
        <div class="admin-page-loader-inner">
            <x-admin-loader-logo
                role="progressbar"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-valuenow="0"
                aria-label="読み込み中"
            />
        </div>
    </div>
    @endif

    <header class="admin-header">
        <div class="flex items-center justify-start px-6 py-3">
            <img
                src="{{ asset('images/care-earth-home-logo.png') }}"
                alt="Care Earth Home"
                class="h-12 w-auto rounded-md bg-white px-2.5 py-1.5 shadow-sm"
            >
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-57px)]">
        <aside class="w-52 shrink-0 bg-white border-r border-slate-200 p-4">
            <nav class="space-y-1">
                <a
                    href="{{ route('admin.applications.index') }}"
                    class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.applications.*') ? 'is-active' : '' }}"
                >
                    申込一覧
                </a>
                <a
                    href="{{ route('admin.screening-completions.index') }}"
                    class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.screening-completions.*') ? 'is-active' : '' }}"
                >
                    審査完了一覧
                </a>
                <a
                    href="{{ route('admin.flow-managements.index') }}"
                    class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.flow-managements.*') ? 'is-active' : '' }}"
                >
                    フロー管理
                </a>
                <a
                    href="{{ route('admin.settlement-managements.index') }}"
                    class="admin-nav-link block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.settlement-managements.*') ? 'is-active' : '' }}"
                >
                    決済金管理
                </a>
            </nav>
        </aside>

        <main id="admin-main-content" class="admin-main-content {{ $adminPageLoaderEnabled ? '' : 'is-visible' }} flex-1 p-8 overflow-x-auto">
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

    @stack('scripts')
    <script>
        function syncStickyCellBackgrounds(table) {
            table.querySelectorAll('tbody tr').forEach((row) => {
                const rowStyle = getComputedStyle(row);
                const backgroundColor = rowStyle.backgroundColor;
                const color = rowStyle.color;
                row.querySelectorAll('.sticky-col').forEach((cell) => {
                    cell.style.backgroundColor = backgroundColor;
                    cell.style.color = color;
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

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.admin-table-sticky').forEach((table) => {
                updateStickyColumnOffsets(table);
            });

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

            const pathEl = document.getElementById('admin-page-loader-path');
            const progressTrack = loader?.querySelector('[role="progressbar"]');
            const loaderStartedAt = performance.now();
            const minLoaderDuration = 600;
            let progress = 0;
            let progressTimer = null;
            let pathLength = 0;

            if (pathEl) {
                pathLength = pathEl.getTotalLength();
                pathEl.style.setProperty('--loader-path-length', String(pathLength));
                pathEl.style.strokeDasharray = String(pathLength);
                pathEl.style.strokeDashoffset = String(pathLength);
            }

            const setProgress = (value) => {
                progress = Math.min(100, Math.max(0, value));

                if (pathEl && pathLength > 0) {
                    pathEl.style.strokeDashoffset = String(pathLength * (1 - progress / 100));
                }

                if (progressTrack) {
                    progressTrack.setAttribute('aria-valuenow', String(Math.round(progress)));
                }
            };

            const startProgress = () => {
                progressTimer = window.setInterval(() => {
                    if (progress >= 90) {
                        return;
                    }

                    const increment = progress < 50 ? 4 : progress < 80 ? 2 : 0.5;
                    setProgress(progress + increment);
                }, 80);
            };

            const finishProgress = (callback) => {
                if (progressTimer !== null) {
                    window.clearInterval(progressTimer);
                    progressTimer = null;
                }

                setProgress(100);
                window.setTimeout(callback, 280);
            };

            const hidePageLoader = () => {
                if (!loader) {
                    mainContent?.classList.add('is-visible');
                    document.documentElement.classList.remove('admin-loading');
                    return;
                }

                const elapsed = performance.now() - loaderStartedAt;
                const remaining = Math.max(0, minLoaderDuration - elapsed);

                finishProgress(() => {
                    if (progressTrack) {
                        progressTrack.setAttribute('aria-valuenow', '100');
                    }

                    window.setTimeout(() => {
                        loader.classList.add('is-hidden');
                        loader.setAttribute('aria-busy', 'false');
                        mainContent?.classList.add('is-visible');
                        document.documentElement.classList.remove('admin-loading');

                        loader.addEventListener('transitionend', () => {
                            loader.remove();
                        }, { once: true });
                    }, remaining);
                });
            };

            startProgress();

            if (document.readyState === 'complete') {
                hidePageLoader();
            } else {
                window.addEventListener('load', hidePageLoader, { once: true });
            }
        });
        @endif

        document.addEventListener('DOMContentLoaded', () => {
            const mainContent = document.getElementById('admin-main-content');
            const navLinks = document.querySelectorAll('.admin-nav-link');

            const setActiveNavLink = (activeLink) => {
                navLinks.forEach((navLink) => {
                    navLink.classList.toggle('is-active', navLink === activeLink);
                });
            };

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

            navLinks.forEach((link) => {
                link.addEventListener('click', (event) => {
                    const targetPath = new URL(link.href).pathname;
                    if (targetPath === window.location.pathname || !mainContent) {
                        return;
                    }

                    event.preventDefault();
                    setActiveNavLink(link);

                    mainContent.classList.add('is-leaving');

                    let navigated = false;
                    const navigate = () => {
                        if (navigated) {
                            return;
                        }
                        navigated = true;
                        sessionStorage.setItem('admin-skip-loader', '1');
                        window.location.href = link.href;
                    };

                    mainContent.addEventListener('animationend', navigate, { once: true });
                    setTimeout(navigate, 300);
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
