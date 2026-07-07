<?php

use App\Models\PropertyDealDraft;
use App\Support\PropertyDealDraftCalculator;
use App\Support\PropertyDealDraftFiscalYear;
use App\Support\PropertyDealDraftPropertyTaxes;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        PropertyDealDraft::query()
            ->with(['adFees', 'propertyTaxes'])
            ->orderBy('id')
            ->get()
            ->each(function (PropertyDealDraft $draft): void {
                PropertyDealDraftPropertyTaxes::sync(
                    $draft,
                    array_map(
                        static fn (int $year): array => [
                            'fiscal_year' => $year,
                            'amount' => PropertyDealDraftPropertyTaxes::amountForYear($draft, $year),
                        ],
                        PropertyDealDraftFiscalYear::visibleYears(),
                    ),
                );

                PropertyDealDraftCalculator::apply($draft);
            });
    }

    public function down(): void
    {
        // 計算値のロールバックは行わない
    }
};
