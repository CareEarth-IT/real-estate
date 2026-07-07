<?php

use App\Models\PropertyDealDraft;
use App\Support\PropertyDealDraftCalculator;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        PropertyDealDraft::query()
            ->with(['adFees', 'propertyTaxes'])
            ->orderBy('id')
            ->get()
            ->each(static fn (PropertyDealDraft $draft): PropertyDealDraft => PropertyDealDraftCalculator::apply($draft));
    }

    public function down(): void
    {
        // 計算値のロールバックは行わない
    }
};
