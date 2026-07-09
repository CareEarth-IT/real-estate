<?php

namespace App\Support;

use App\Models\Application;
use App\Models\Customer;
use Illuminate\Support\Collection;

class ManagementCompanySuggestions
{
    /**
     * @return list<string>
     */
    public static function search(string $query, int $limit = 10): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 2) {
            return [];
        }

        $like = '%'.addcslashes($query, '%_\\').'%';

        $fromApplications = Application::query()
            ->whereNotNull('management_company_name')
            ->where('management_company_name', '!=', '')
            ->where('management_company_name', 'like', $like)
            ->distinct()
            ->pluck('management_company_name');

        $fromCustomers = Customer::query()
            ->whereNotNull('management_company')
            ->where('management_company', '!=', '')
            ->where('management_company', 'like', $like)
            ->distinct()
            ->pluck('management_company');

        return $fromApplications
            ->merge($fromCustomers)
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn (string $name) => $name !== '')
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->take($limit)
            ->all();
    }
}
