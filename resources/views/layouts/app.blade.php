<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', '賃貸申込フォーム')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/care-earth-home-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/care-earth-home-logo.png') }}">
    @include('partials.app-url-helpers')
    <script>
        (function () {
            if (sessionStorage.getItem('form-page-transition') === '1') {
                document.documentElement.classList.add('form-page-pre-enter');
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
        .site-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: var(--color-header-gradient);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        }

        .flatpickr-calendar {
            border-radius: 0.75rem;
            border-color: rgb(226 232 240);
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15);
        }
        .flatpickr-months .flatpickr-month {
            height: 44px;
        }
        .flatpickr-current-month {
            padding-top: 0.35rem;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months,
        .flatpickr-current-month input.cur-year {
            font-size: 1rem;
            font-weight: 600;
        }
        .flatpickr-day.selected,
        .flatpickr-day.selected:hover {
            background: rgb(37 99 235);
            border-color: rgb(37 99 235);
        }

        .form-reveal {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 0.55s ease-out, transform 0.55s ease-out;
        }

        .form-reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (prefers-reduced-motion: reduce) {
            .form-reveal {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }

        html.form-page-pre-enter #form-page-content {
            opacity: 0;
        }

        .form-page-content.is-entering {
            animation: formPageFadeIn 0.45s ease-out forwards;
        }

        .form-page-content.is-leaving {
            animation: formPageFadeOut 0.28s ease-in forwards;
            pointer-events: none;
        }

        @keyframes formPageFadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes formPageFadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html.form-page-pre-enter #form-page-content {
                opacity: 1;
            }

            .form-page-content.is-entering,
            .form-page-content.is-leaving {
                animation: none;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    <header class="site-header">
        <div class="flex items-center justify-start px-6 py-3">
            <img
                src="{{ asset('images/care-earth-home-logo.png') }}"
                alt="Care Earth Home"
                class="h-12 w-auto rounded-md bg-white px-2.5 py-1.5 shadow-sm"
            >
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <div id="form-page-content" class="form-page-content">
            @if (session('success'))
                <div class="mb-6 rounded-lg bg-[#d4edf7] border border-[#b8dce8] px-4 py-3 text-[#2d6b8a] text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const main = document.getElementById('form-page-content');
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const initFormReveal = () => {
                if (!main) {
                    return;
                }

                const targets = main.querySelectorAll(':scope > .mb-8, form section, form > .flex.justify-end');
                if (!targets.length) {
                    return;
                }

                if (reducedMotion) {
                    targets.forEach((element) => element.classList.add('form-reveal', 'is-visible'));
                    return;
                }

                const revealTarget = (element, observer) => {
                    element.classList.add('is-visible');
                    observer.unobserve(element);
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            revealTarget(entry.target, observer);
                        }
                    });
                }, {
                    threshold: 0.08,
                    rootMargin: '0px 0px -24px 0px',
                });

                targets.forEach((element) => {
                    element.classList.add('form-reveal');
                    observer.observe(element);
                });

                requestAnimationFrame(() => {
                    targets.forEach((element) => {
                        if (element.classList.contains('is-visible')) {
                            return;
                        }

                        const rect = element.getBoundingClientRect();
                        if (rect.top < window.innerHeight * 0.92 && rect.bottom > 0) {
                            revealTarget(element, observer);
                        }
                    });
                });
            };

            const initFormPageTransition = () => {
                if (!main) {
                    initFormReveal();
                    return;
                }

                let shouldEnter = false;

                if (!reducedMotion && sessionStorage.getItem('form-page-transition') === '1') {
                    sessionStorage.removeItem('form-page-transition');
                    document.documentElement.classList.remove('form-page-pre-enter');
                    main.classList.add('is-entering');
                    shouldEnter = true;
                } else {
                    document.documentElement.classList.remove('form-page-pre-enter');
                }

                const startFormReveal = () => {
                    initFormReveal();
                };

                if (shouldEnter) {
                    main.addEventListener('animationend', () => {
                        main.classList.remove('is-entering');
                        startFormReveal();
                    }, { once: true });
                } else {
                    startFormReveal();
                }

                document.querySelectorAll('main form').forEach((form) => {
                    form.addEventListener('submit', (event) => {
                        if (reducedMotion || form.dataset.transitionSubmitting === '1') {
                            return;
                        }

                        event.preventDefault();
                        form.dataset.transitionSubmitting = '1';
                        sessionStorage.setItem('form-page-transition', '1');
                        main.classList.add('is-leaving');

                        let hasSubmitted = false;
                        const submitForm = () => {
                            if (hasSubmitted) {
                                return;
                            }
                            hasSubmitted = true;
                            form.submit();
                        };

                        main.addEventListener('animationend', submitForm, { once: true });
                        setTimeout(submitForm, 400);
                    });
                });
            };

            initFormPageTransition();

            if (typeof flatpickr === 'undefined') {
                return;
            }

            flatpickr.localize(flatpickr.l10ns.ja);

            const invertFlatpickrSpinnerArrows = (instance) => {
                const calendar = instance.calendarContainer;
                if (!calendar) {
                    return;
                }

                calendar.querySelectorAll('.numInputWrapper').forEach((wrapper) => {
                    if (wrapper.dataset.arrowsInverted) {
                        return;
                    }

                    const up = wrapper.querySelector('.arrowUp');
                    const down = wrapper.querySelector('.arrowDown');
                    if (!up || !down) {
                        return;
                    }

                    up.classList.remove('arrowUp');
                    up.classList.add('arrowDown');
                    down.classList.remove('arrowDown');
                    down.classList.add('arrowUp');
                    wrapper.dataset.arrowsInverted = '1';
                });
            };

            document.querySelectorAll('[data-date-picker]').forEach((element) => {
                flatpickr(element, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'Y/m/d',
                    allowInput: true,
                    monthSelectorType: 'dropdown',
                    disableMobile: true,
                    onReady(_selectedDates, _dateStr, instance) {
                        if (instance.altInput) {
                            instance.altInput.id = instance.input.id;
                            instance.input.removeAttribute('id');
                        }

                        invertFlatpickrSpinnerArrows(instance);
                    },
                    onOpen(_selectedDates, _dateStr, instance) {
                        invertFlatpickrSpinnerArrows(instance);
                    },
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
