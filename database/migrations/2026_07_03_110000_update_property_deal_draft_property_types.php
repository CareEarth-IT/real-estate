<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('property_deal_drafts')
            ->where('property_type', 'detached')
            ->update(['property_type' => 'detached_house']);
    }

    public function down(): void
    {
        DB::table('property_deal_drafts')
            ->where('property_type', 'detached_house')
            ->update(['property_type' => 'detached']);
    }
};
