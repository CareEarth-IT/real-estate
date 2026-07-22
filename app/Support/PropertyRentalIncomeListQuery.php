<?php

namespace App\Support;

use App\Models\PropertyRentalIncome;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class PropertyRentalIncomeListQuery
{
    /** @return array{records: \Illuminate\Database\Eloquent\Collection<int, PropertyRentalIncome>, search: string, paymentStatus: ?string, sort: string, sortDirection: 'asc'|'desc'} */
    public static function resolve(Request $request, ?int $paymentMonth = null): array
    {
        $search = trim((string) $request->query('search', ''));
        $paymentStatus = self::resolvePaymentStatusFilter($request);
        $isAllList = $paymentMonth === null;
        $sortState = PropertyRentalIncomeListSort::resolve($request, $isAllList);

        $query = PropertyRentalIncome::query()->with('nextMonthCopy');

        if ($paymentMonth !== null) {
            $query->where('payment_month', $paymentMonth);
        }

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('contractor', 'like', '%'.$search.'%')
                    ->orWhere('property_name', 'like', '%'.$search.'%');
            });
        }

        if ($paymentStatus !== null && $paymentStatus !== 'terminated') {
            $query->where('payment_status', $paymentStatus);
        }

        if ($isAllList) {
            $records = PropertyRentalIncomeListSort::applyAndGet(
                $query,
                $sortState['sort'],
                $sortState['direction'],
            );
        } else {
            $query
                ->orderByDesc('payment_on')
                ->orderByDesc('created_on')
                ->orderByDesc('id');
            $records = $query->get();
        }

        return [
            'records' => $records,
            'search' => $search,
            'paymentStatus' => $paymentStatus,
            'sort' => $sortState['sort'],
            'sortDirection' => $sortState['direction'],
        ];
    }

    /** @param array<string, scalar|null> $params */
    public static function listUrl(string $routeName, array $params = []): string
    {
        return route($routeName, array_filter(
            $params,
            static fn ($value): bool => $value !== null && $value !== '',
        ));
    }

    private static function resolvePaymentStatusFilter(Request $request): ?string
    {
        return self::paymentStatusFromRequest($request);
    }

    public static function paymentStatusFromRequest(Request $request): ?string
    {
        $value = (string) $request->query('payment_status', '');

        if ($value === '') {
            return null;
        }

        $allowed = array_keys(config('property-rental-income.payment_status_filters', config('property-rental-income.payment_statuses', [])));

        return in_array($value, $allowed, true) ? $value : null;
    }
}
