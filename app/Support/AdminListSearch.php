<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class AdminListSearch
{
    public static function term(?string $search): string
    {
        return trim((string) $search);
    }

    public static function likePattern(string $search): string
    {
        return '%'.addcslashes($search, '%_\\').'%';
    }

    /**
     * @param  array<int, string|callable(Builder, string, string): void>  $conditions
     */
    public static function apply(Builder $query, ?string $search, array $conditions): Builder
    {
        $search = self::term($search);

        if ($search === '') {
            return $query;
        }

        $like = self::likePattern($search);

        return $query->where(function (Builder $nested) use ($conditions, $like, $search) {
            foreach ($conditions as $condition) {
                if (is_callable($condition)) {
                    $condition($nested, $like, $search);

                    continue;
                }

                $nested->orWhere($condition, 'like', $like);
            }
        });
    }

    public static function applyToApplication(Builder $query, ?string $search): Builder
    {
        return self::apply($query, $search, [
            'staff_in_charge',
            'property_name_room',
            'management_company_name',
            'application_method',
            'status',
            'memo',
            'property_documents_url',
            'appliance_support_notes',
            fn (Builder $nested, string $like) => $nested->orWhereRaw('CAST(advertising_fee AS CHAR) LIKE ?', [$like]),
            fn (Builder $nested, string $like) => $nested->orWhereRaw("DATE_FORMAT(scheduled_move_in_date, '%Y/%m/%d') LIKE ?", [$like]),
            fn (Builder $nested, string $like) => $nested->orWhereRaw("DATE_FORMAT(created_at, '%Y/%m/%d %H:%i') LIKE ?", [$like]),
        ]);
    }

    public static function applyToScreeningCompletion(Builder $query, ?string $search): Builder
    {
        return self::apply($query, $search, [
            'screening_completions.staff_in_charge',
            'screening_completions.property_name_room',
            'screening_completions.application_method',
            fn (Builder $nested, string $like) => $nested->orWhereRaw("DATE_FORMAT(applications.created_at, '%Y/%m/%d %H:%i') LIKE ?", [$like]),
        ]);
    }

    public static function applyToFlowManagement(Builder $query, ?string $search): Builder
    {
        return self::apply($query, $search, [
            'flow_managements.staff_in_charge',
            'flow_managements.property_name_room',
            'flow_managements.application_method',
            'flow_managements.memo',
            'flow_managements.document_deadline',
            'flow_managements.ad_fee_invoice_creation',
            fn (Builder $nested, string $like) => $nested->orWhereRaw("DATE_FORMAT(flow_managements.move_in_date, '%Y/%m/%d') LIKE ?", [$like]),
            fn (Builder $nested, string $like) => $nested->orWhereRaw("DATE_FORMAT(flow_managements.scheduled_visit_date, '%Y/%m/%d') LIKE ?", [$like]),
            fn (Builder $nested, string $like) => $nested->orWhereRaw("DATE_FORMAT(flow_managements.key_handover_date, '%Y/%m/%d') LIKE ?", [$like]),
            fn (Builder $nested, string $like) => $nested->orWhereRaw("DATE_FORMAT(applications.created_at, '%Y/%m/%d %H:%i') LIKE ?", [$like]),
        ]);
    }

    public static function applyToSettlementManagement(Builder $query, ?string $search): Builder
    {
        return self::apply($query, $search, [
            'settlement_managements.staff_in_charge',
            'settlement_managements.property_name',
            'settlement_managements.management_number',
            'settlement_managements.earned_points',
            'settlement_managements.remarks',
            fn (Builder $nested, string $like) => $nested->orWhereRaw('CAST(settlement_managements.estimated_sales AS CHAR) LIKE ?', [$like]),
            fn (Builder $nested, string $like) => $nested->orWhereRaw('CAST(settlement_managements.sales_including_tax AS CHAR) LIKE ?', [$like]),
            fn (Builder $nested, string $like) => $nested->orWhereRaw('CAST(settlement_managements.sales_excluding_tax AS CHAR) LIKE ?', [$like]),
            fn (Builder $nested, string $like) => $nested->orWhereRaw("DATE_FORMAT(settlement_managements.contract_date, '%Y/%m/%d') LIKE ?", [$like]),
            fn (Builder $nested, string $like) => $nested->orWhereRaw("DATE_FORMAT(settlement_managements.settlement_transfer_date, '%Y/%m/%d') LIKE ?", [$like]),
            fn (Builder $nested, string $like) => $nested->orWhereRaw("DATE_FORMAT(applications.created_at, '%Y/%m/%d %H:%i') LIKE ?", [$like]),
        ]);
    }
}
