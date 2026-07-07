<?php

namespace App\Support;

use App\Models\PropertyRentalIncome;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

final class PropertyRentalIncomeListSort
{
    /** @return array<string, string> */
    public static function sortableColumns(): array
    {
        return config('property-rental-income.all_sortable_columns', []);
    }

    /** @return array{sort: string, direction: 'asc'|'desc'} */
    public static function resolve(Request $request, bool $isAllList): array
    {
        if (! $isAllList) {
            return [
                'sort' => 'payment_on',
                'direction' => 'desc',
            ];
        }

        $allowed = array_keys(self::sortableColumns());
        $defaultSort = (string) config('property-rental-income.default_all_sort', 'payment_on');
        $defaultDirection = strtolower((string) config('property-rental-income.default_all_direction', 'desc')) === 'asc'
            ? 'asc'
            : 'desc';

        $sort = (string) $request->query('sort', '');
        $direction = strtolower((string) $request->query('direction', ''));

        if ($sort === '' || ! in_array($sort, $allowed, true)) {
            return [
                'sort' => $defaultSort,
                'direction' => $defaultDirection,
            ];
        }

        return [
            'sort' => $sort,
            'direction' => $direction === 'asc' ? 'asc' : 'desc',
        ];
    }

    /**
     * @param  Builder<PropertyRentalIncome>  $query
     * @return Collection<int, PropertyRentalIncome>
     */
    public static function applyAndGet(Builder $query, string $sort, string $direction): Collection
    {
        if ($sort === 'contractor') {
            return self::sortByContractor($query->get(), $direction);
        }

        self::applyToQuery($query, $sort, $direction);

        return $query->get();
    }

    /** @param  Builder<PropertyRentalIncome>  $query */
    private static function applyToQuery(Builder $query, string $sort, string $direction): void
    {
        match ($sort) {
            'created_on' => $query
                ->orderByRaw('created_on IS NULL')
                ->orderBy('created_on', $direction),
            'payment_month' => $query->orderBy('payment_month', $direction),
            'payment_on' => $query
                ->orderByRaw('payment_on IS NULL')
                ->orderBy('payment_on', $direction),
            default => $query->orderBy('id', $direction),
        };

        $query->orderBy('id', $direction);
    }

    /**
     * @param  Collection<int, PropertyRentalIncome>  $records
     * @return Collection<int, PropertyRentalIncome>
     */
    private static function sortByContractor(Collection $records, string $direction): Collection
    {
        $collator = extension_loaded('intl') ? collator_create('ja_JP') : null;

        $sorted = $records->sort(function (PropertyRentalIncome $a, PropertyRentalIncome $b) use ($collator, $direction): int {
            $left = (string) ($a->contractor ?? '');
            $right = (string) ($b->contractor ?? '');

            $leftEmpty = $left === '';
            $rightEmpty = $right === '';

            if ($leftEmpty !== $rightEmpty) {
                return $leftEmpty ? 1 : -1;
            }

            if ($leftEmpty) {
                return $a->id <=> $b->id;
            }

            if ($collator !== null) {
                $cmp = collator_compare($collator, $left, $right);

                if ($cmp !== false) {
                    return $direction === 'desc' ? -$cmp : $cmp;
                }
            }

            $cmp = strcmp($left, $right);

            return $direction === 'desc' ? -$cmp : $cmp;
        });

        return $sorted->values();
    }

    /** @param  array<string, scalar|null>  $params */
    public static function headerUrl(
        string $routeName,
        array $params,
        string $column,
        ?string $currentSort,
        ?string $currentDirection,
    ): string {
        $direction = 'asc';

        if ($currentSort === $column && $currentDirection === 'asc') {
            $direction = 'desc';
        }

        return PropertyRentalIncomeListQuery::listUrl($routeName, array_merge($params, [
            'sort' => $column,
            'direction' => $direction,
        ]));
    }
}
