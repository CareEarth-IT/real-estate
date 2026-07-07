<?php

namespace App\Support;

use App\Models\PropertyDealDraft;

final class PropertyDealDraftCalculator
{
    /** @return list<string> */
    public static function totalCostKeys(): array
    {
        return [
            'property_price',
            'registration_license_tax',
            'judicial_scrivener_fee',
            'postage',
            'pre_registration_info_fee',
            'post_registration_certificate_fee',
            'withholding_income_tax',
            'purchase_brokerage_fee',
            'building_consumption_tax',
            'real_estate_acquisition_tax',
            'renovation_cost',
            'contingency_fund',
        ];
    }

    /** @return list<string> */
    public static function computedKeys(): array
    {
        return [
            'total_cost',
            'cost_ratio',
            'gross_profit_margin',
            'total_selling_admin_expenses',
            'estimated_operating_profit_margin',
            'expected_surface_yield',
            'estimated_ownership_yield',
        ];
    }

    /** @return array<string, int|float|null> */
    public static function calculate(PropertyDealDraft $draft): array
    {
        $draft->loadMissing(['adFees', 'propertyTaxes']);

        $totalCost = 0;

        foreach (self::totalCostKeys() as $key) {
            $totalCost += (int) ($draft->{$key} ?? 0);
        }

        $visibleFiscalYears = PropertyDealDraftFiscalYear::visibleYears();

        $totalCost += (int) $draft->propertyTaxes
            ->whereIn('fiscal_year', $visibleFiscalYears)
            ->sum(static fn ($tax): int => (int) ($tax->amount ?? 0));

        $sellingPrice = (int) ($draft->expected_selling_price ?? 0);

        $costRatio = null;
        $grossProfitMargin = null;

        if ($sellingPrice > 0) {
            $ratio = $totalCost / $sellingPrice;
            $costRatio = round($ratio * 100, 1);
            $grossProfitMargin = round((1 - $ratio) * 100, 1);
        }

        $adFeesTotal = (int) $draft->adFees->sum(static fn ($fee): int => (int) ($fee->amount ?? 0));

        $totalSellingAdminExpenses = $adFeesTotal
            + (int) ($draft->sale_brokerage_fee ?? 0)
            + (int) ($draft->contract_stamp_duty ?? 0)
            + (int) ($draft->receipt_stamp_duty ?? 0);

        $estimatedOperatingProfitMargin = null;

        if ($sellingPrice > 0) {
            $estimatedOperatingProfitMargin = round(
                (1 - (($totalCost + $totalSellingAdminExpenses) / $sellingPrice)) * 100,
                1,
            );
        }

        $expectedRent = (int) ($draft->expected_rent ?? 0);
        $annualRent = $expectedRent * 12;

        $expectedSurfaceYield = null;
        $estimatedOwnershipYield = null;

        if ($expectedRent > 0) {
            if ($sellingPrice > 0) {
                $expectedSurfaceYield = round(($annualRent / $sellingPrice) * 100, 1);
            }

            if ($totalCost > 0) {
                $estimatedOwnershipYield = round(($annualRent / $totalCost) * 100, 1);
            }
        }

        return [
            'total_cost' => $totalCost,
            'cost_ratio' => $costRatio,
            'gross_profit_margin' => $grossProfitMargin,
            'total_selling_admin_expenses' => $totalSellingAdminExpenses,
            'estimated_operating_profit_margin' => $estimatedOperatingProfitMargin,
            'expected_surface_yield' => $expectedSurfaceYield,
            'estimated_ownership_yield' => $estimatedOwnershipYield,
        ];
    }

    public static function apply(PropertyDealDraft $draft): PropertyDealDraft
    {
        $draft->fill(self::calculate($draft));
        $draft->save();

        return $draft->fresh(['adFees', 'propertyTaxes']);
    }

    /** @return array<string, int|float|null> */
    public static function computedForResponse(PropertyDealDraft $draft): array
    {
        return self::calculate($draft->loadMissing(['adFees', 'propertyTaxes']));
    }
}
