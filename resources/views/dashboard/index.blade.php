@extends('layouts.admin')

@section('title', 'ホーム — ' . config('app.name'))

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">ホーム</h2>
        <p class="mt-1 text-sm text-slate-500">担当者別の業績一覧と、審査OKの推移です。</p>
    </div>

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <div class="w-fit max-w-full shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-auto text-left text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-3 py-2.5 font-semibold whitespace-nowrap">担当者</th>
                            <th class="px-3 py-2.5 font-semibold whitespace-nowrap text-right">申込数</th>
                            <th class="px-3 py-2.5 font-semibold whitespace-nowrap text-right">キャンセル数</th>
                            <th class="px-3 py-2.5 font-semibold whitespace-nowrap text-right">キャンセル率</th>
                            <th class="px-3 py-2.5 font-semibold whitespace-nowrap text-right">審査OK数</th>
                            <th class="px-3 py-2.5 font-semibold whitespace-nowrap text-right">審査OK率</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($staffStats as $row)
                            <tr class="bg-white hover:bg-slate-50">
                                <td class="px-3 py-2.5 font-medium text-slate-900 whitespace-nowrap">{{ $row['name'] }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums text-slate-800">{{ number_format($row['applications_count']) }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums text-slate-800">{{ number_format($row['cancelled_count']) }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums text-slate-800">{{ number_format($row['cancel_rate'], 1) }}%</td>
                                <td class="px-3 py-2.5 text-right tabular-nums text-slate-800">{{ number_format($row['screening_ok_count']) }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums text-slate-800">{{ number_format($row['screening_ok_rate'], 1) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-slate-500">
                                    ユーザー管理に名前付きの担当者が登録されていません。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($staffStats->isNotEmpty())
                        <tfoot class="bg-slate-50 border-t border-slate-200">
                            <tr>
                                <th class="px-3 py-2.5 text-left font-semibold text-slate-900">合計</th>
                                <th class="px-3 py-2.5 text-right font-semibold tabular-nums text-slate-900">{{ number_format($totals['applications_count']) }}</th>
                                <th class="px-3 py-2.5 text-right font-semibold tabular-nums text-slate-900">{{ number_format($totals['cancelled_count']) }}</th>
                                <th class="px-3 py-2.5 text-right font-semibold tabular-nums text-slate-900">{{ number_format($totals['cancel_rate'], 1) }}%</th>
                                <th class="px-3 py-2.5 text-right font-semibold tabular-nums text-slate-900">{{ number_format($totals['screening_ok_count']) }}</th>
                                <th class="px-3 py-2.5 text-right font-semibold tabular-nums text-slate-900">{{ number_format($totals['screening_ok_rate'], 1) }}%</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div class="min-w-0 flex-1 overflow-hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">審査OK数（{{ $screeningOkChart['period_label'] }}）</h3>
                    <p class="mt-0.5 text-xs text-slate-500">{{ $screeningOkChart['range_label'] }}・審査OKを押した日で集計</p>
                </div>
                <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-0.5 text-sm">
                    @foreach ([
                        'day' => '日別',
                        'month' => '月別',
                        'year' => '年別',
                    ] as $periodKey => $periodName)
                        <a
                            href="{{ route('home', ['chart_period' => $periodKey]) }}"
                            @class([
                                'rounded-md px-3 py-1.5 font-medium transition',
                                'bg-white text-slate-900 shadow-sm' => $chartPeriod === $periodKey,
                                'text-slate-500 hover:text-slate-800' => $chartPeriod !== $periodKey,
                            ])
                        >{{ $periodName }}</a>
                    @endforeach
                </div>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="screening-ok-chart" aria-label="審査OK数の折れ線グラフ"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const canvas = document.getElementById('screening-ok-chart');
            if (!canvas || typeof Chart === 'undefined') {
                return;
            }

            const labels = @json($screeningOkChart['labels']);
            const values = @json($screeningOkChart['values']);

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: '審査OK数',
                        data: values,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.12)',
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#2563eb',
                        tension: 0.25,
                        fill: true,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label(context) {
                                    return '審査OK: ' + context.parsed.y + '件';
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 10,
                                color: '#64748b',
                                font: { size: 11 },
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                stepSize: 1,
                                color: '#64748b',
                                font: { size: 11 },
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.25)',
                            },
                        },
                    },
                },
            });
        })();
    </script>
@endpush
