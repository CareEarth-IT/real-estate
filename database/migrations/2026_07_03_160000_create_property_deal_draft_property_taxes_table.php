<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('property_deal_draft_property_taxes');

        Schema::create('property_deal_draft_property_taxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_deal_draft_id');
            $table->unsignedSmallInteger('fiscal_year')->comment('令和年度');
            $table->integer('amount')->nullable()->comment('固定資産税');
            $table->timestamps();

            $table->foreign('property_deal_draft_id', 'deal_draft_property_tax_draft_fk')
                ->references('id')
                ->on('property_deal_drafts')
                ->cascadeOnDelete();

            $table->unique(['property_deal_draft_id', 'fiscal_year'], 'deal_draft_property_tax_unique');
        });

        if (Schema::hasColumn('property_deal_drafts', 'property_tax_r7')) {
            $drafts = DB::table('property_deal_drafts')->get(['id', 'property_tax_r7', 'property_tax_r8']);

            foreach ($drafts as $draft) {
                $rows = [
                    7 => $draft->property_tax_r7,
                    8 => $draft->property_tax_r8,
                ];

                foreach ($rows as $fiscalYear => $amount) {
                    if ($amount === null) {
                        continue;
                    }

                    DB::table('property_deal_draft_property_taxes')->insert([
                        'property_deal_draft_id' => $draft->id,
                        'fiscal_year' => $fiscalYear,
                        'amount' => $amount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            Schema::table('property_deal_drafts', function (Blueprint $table) {
                $table->dropColumn(['property_tax_r7', 'property_tax_r8']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('property_deal_drafts', function (Blueprint $table) {
            $table->integer('property_tax_r7')->nullable()->after('purchase_brokerage_fee');
            $table->integer('property_tax_r8')->nullable()->after('property_tax_r7');
        });

        $groups = DB::table('property_deal_draft_property_taxes')
            ->orderBy('property_deal_draft_id')
            ->orderBy('fiscal_year')
            ->get()
            ->groupBy('property_deal_draft_id');

        foreach ($groups as $draftId => $taxes) {
            $values = ['property_tax_r7' => null, 'property_tax_r8' => null];

            foreach ($taxes as $tax) {
                if ((int) $tax->fiscal_year === 7) {
                    $values['property_tax_r7'] = $tax->amount;
                }

                if ((int) $tax->fiscal_year === 8) {
                    $values['property_tax_r8'] = $tax->amount;
                }
            }

            DB::table('property_deal_drafts')->where('id', $draftId)->update($values);
        }

        Schema::dropIfExists('property_deal_draft_property_taxes');
    }
};
