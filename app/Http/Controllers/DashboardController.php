<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\CareEarthUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const CHART_PERIODS = ['day', 'month', 'year'];

    private const CHART_DAY_SPAN = 30;

    private const CHART_MONTH_SPAN = 12;

    private const CHART_YEAR_SPAN = 5;

    public function index(Request $request): View
    {
        $staffStats = $this->staffPerformanceRows();
        $chartPeriod = $this->normalizeChartPeriod($request->query('chart_period'));

        return view('dashboard.index', [
            'staffStats' => $staffStats,
            'totals' => $this->totalsFromRows($staffStats),
            'screeningOkChart' => $this->screeningOkChartData($chartPeriod),
            'chartPeriod' => $chartPeriod,
        ]);
    }

    /**
     * @return Collection<int, array{
     *     name: string,
     *     applications_count: int,
     *     cancelled_count: int,
     *     cancel_rate: float,
     *     screening_ok_count: int,
     *     screening_ok_rate: float
     * }>
     */
    private function staffPerformanceRows(): Collection
    {
        $users = CareEarthUser::query()
            ->where('show_performance', true)
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name']);

        $applicationStats = Application::query()
            ->selectRaw('staff_in_charge')
            ->selectRaw('COUNT(*) as applications_count')
            ->selectRaw('SUM(CASE WHEN is_cancelled = 1 THEN 1 ELSE 0 END) as cancelled_count')
            ->selectRaw('SUM(CASE WHEN screening_ok = 1 THEN 1 ELSE 0 END) as screening_ok_count')
            ->whereNotNull('staff_in_charge')
            ->where('staff_in_charge', '!=', '')
            ->groupBy('staff_in_charge')
            ->get()
            ->keyBy(fn ($row) => (string) $row->staff_in_charge);

        return $users->map(function (CareEarthUser $user) use ($applicationStats) {
            $stats = $applicationStats->get($user->name);
            $applicationsCount = (int) ($stats->applications_count ?? 0);
            $cancelledCount = (int) ($stats->cancelled_count ?? 0);
            $screeningOkCount = (int) ($stats->screening_ok_count ?? 0);

            return [
                'name' => (string) $user->name,
                'applications_count' => $applicationsCount,
                'cancelled_count' => $cancelledCount,
                'cancel_rate' => $this->rate($cancelledCount, $applicationsCount),
                'screening_ok_count' => $screeningOkCount,
                'screening_ok_rate' => $this->rate($screeningOkCount, $applicationsCount),
            ];
        })->values();
    }

    /**
     * @param  Collection<int, array{applications_count: int, cancelled_count: int, screening_ok_count: int}>  $rows
     * @return array{applications_count: int, cancelled_count: int, cancel_rate: float, screening_ok_count: int, screening_ok_rate: float}
     */
    private function totalsFromRows(Collection $rows): array
    {
        $applicationsCount = (int) $rows->sum('applications_count');
        $cancelledCount = (int) $rows->sum('cancelled_count');
        $screeningOkCount = (int) $rows->sum('screening_ok_count');

        return [
            'applications_count' => $applicationsCount,
            'cancelled_count' => $cancelledCount,
            'cancel_rate' => $this->rate($cancelledCount, $applicationsCount),
            'screening_ok_count' => $screeningOkCount,
            'screening_ok_rate' => $this->rate($screeningOkCount, $applicationsCount),
        ];
    }

    private function normalizeChartPeriod(mixed $period): string
    {
        $period = is_string($period) ? $period : 'day';

        return in_array($period, self::CHART_PERIODS, true) ? $period : 'day';
    }

    /**
     * @return array{labels: list<string>, values: list<int>, period: string, period_label: string, range_label: string}
     */
    private function screeningOkChartData(string $period): array
    {
        return match ($period) {
            'month' => $this->buildPeriodChart(
                period: 'month',
                periodLabel: '月別',
                groupExpression: "DATE_FORMAT(screening_ok_at, '%Y-%m')",
                from: Carbon::today()->startOfMonth()->subMonths(self::CHART_MONTH_SPAN - 1),
                to: Carbon::today()->endOfMonth(),
                step: fn (Carbon $cursor) => $cursor->addMonth(),
                keyFormat: 'Y-m',
                labelFormat: 'Y/n',
                rangeLabel: '直近'.self::CHART_MONTH_SPAN.'ヶ月',
            ),
            'year' => $this->buildPeriodChart(
                period: 'year',
                periodLabel: '年別',
                groupExpression: 'YEAR(screening_ok_at)',
                from: Carbon::today()->startOfYear()->subYears(self::CHART_YEAR_SPAN - 1),
                to: Carbon::today()->endOfYear(),
                step: fn (Carbon $cursor) => $cursor->addYear(),
                keyFormat: 'Y',
                labelFormat: 'Y年',
                rangeLabel: '直近'.self::CHART_YEAR_SPAN.'年',
            ),
            default => $this->buildPeriodChart(
                period: 'day',
                periodLabel: '日別',
                groupExpression: 'DATE(screening_ok_at)',
                from: Carbon::today()->subDays(self::CHART_DAY_SPAN - 1)->startOfDay(),
                to: Carbon::today()->endOfDay(),
                step: fn (Carbon $cursor) => $cursor->addDay(),
                keyFormat: 'Y-m-d',
                labelFormat: 'n/j',
                rangeLabel: '直近'.self::CHART_DAY_SPAN.'日',
            ),
        };
    }

    /**
     * @param  callable(Carbon): Carbon  $step
     * @return array{labels: list<string>, values: list<int>, period: string, period_label: string, range_label: string}
     */
    private function buildPeriodChart(
        string $period,
        string $periodLabel,
        string $groupExpression,
        Carbon $from,
        Carbon $to,
        callable $step,
        string $keyFormat,
        string $labelFormat,
        string $rangeLabel,
    ): array {
        $counts = Application::query()
            ->where('screening_ok', true)
            ->whereNotNull('screening_ok_at')
            ->whereBetween('screening_ok_at', [$from, $to])
            ->selectRaw("{$groupExpression} as period_key")
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('period_key')
            ->pluck('cnt', 'period_key')
            ->mapWithKeys(fn ($cnt, $key) => [(string) $key => (int) $cnt]);

        $labels = [];
        $values = [];

        for ($cursor = $from->copy(); $cursor->lte($to); $step($cursor)) {
            $key = $cursor->format($keyFormat);
            $labels[] = $cursor->format($labelFormat);
            $values[] = (int) ($counts[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'period' => $period,
            'period_label' => $periodLabel,
            'range_label' => $rangeLabel,
        ];
    }

    private function rate(int $part, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return round(($part / $total) * 100, 1);
    }
}
