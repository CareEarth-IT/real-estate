<?php

use App\Models\PropertyDealDraft;
use App\Support\PropertyDealDraftCalculator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $draft = DB::table('property_deal_drafts')->where('case_number', 'K0001')->first();

        if ($draft !== null) {
            $exists = DB::table('property_deal_draft_property_taxes')
                ->where('property_deal_draft_id', $draft->id)
                ->where('fiscal_year', 7)
                ->exists();

            if (! $exists) {
                DB::table('property_deal_draft_property_taxes')->insert([
                    'property_deal_draft_id' => $draft->id,
                    'fiscal_year' => 7,
                    'amount' => 6863,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        PropertyDealDraft::query()
            ->with(['adFees', 'propertyTaxes'])
            ->orderBy('id')
            ->get()
            ->each(static fn (PropertyDealDraft $draft): PropertyDealDraft => PropertyDealDraftCalculator::apply($draft));
    }

    public function down(): void
    {
        // 復元データのロールバックは行わない
    }
};
