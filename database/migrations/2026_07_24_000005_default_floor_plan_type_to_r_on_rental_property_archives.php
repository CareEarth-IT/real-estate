<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rental_property_archives') || ! Schema::hasColumn('rental_property_archives', 'floor_plan_type')) {
            return;
        }

        DB::table('rental_property_archives')
            ->whereNull('floor_plan_type')
            ->orWhere('floor_plan_type', '')
            ->update(['floor_plan_type' => 'R']);
    }

    public function down(): void
    {
        // no-op
    }
};
