<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('property_deal_draft_ad_fees')
            ->where('agency_name', '健美家（月額×掲載月）')
            ->update(['agency_name' => '健美家']);
    }

    public function down(): void
    {
        DB::table('property_deal_draft_ad_fees')
            ->where('agency_name', '健美家')
            ->update(['agency_name' => '健美家（月額×掲載月）']);
    }
};
