<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_deal_draft_ad_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_deal_draft_id')
                ->constrained('property_deal_drafts')
                ->cascadeOnDelete();
            $table->string('agency_name', 255)->comment('仲介業者名');
            $table->integer('amount')->nullable()->comment('金額');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        if (Schema::hasColumn('property_deal_drafts', 'kenbiya_ad_fee')) {
            $drafts = DB::table('property_deal_drafts')
                ->whereNotNull('kenbiya_ad_fee')
                ->get(['id', 'kenbiya_ad_fee']);

            foreach ($drafts as $draft) {
                DB::table('property_deal_draft_ad_fees')->insert([
                    'property_deal_draft_id' => $draft->id,
                    'agency_name' => '健美家',
                    'amount' => $draft->kenbiya_ad_fee,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('property_deal_drafts', function (Blueprint $table) {
                $table->dropColumn('kenbiya_ad_fee');
            });
        }
    }

    public function down(): void
    {
        Schema::table('property_deal_drafts', function (Blueprint $table) {
            $table->integer('kenbiya_ad_fee')->nullable()->after('gross_profit_margin');
        });

        $groups = DB::table('property_deal_draft_ad_fees')
            ->orderBy('property_deal_draft_id')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('property_deal_draft_id');

        foreach ($groups as $draftId => $fees) {
            $first = $fees->first();
            DB::table('property_deal_drafts')
                ->where('id', $draftId)
                ->update(['kenbiya_ad_fee' => $first->amount ?? null]);
        }

        Schema::dropIfExists('property_deal_draft_ad_fees');
    }
};
