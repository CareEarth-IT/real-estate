<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    <main class="max-w-4xl mx-auto px-4 py-8">
        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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
