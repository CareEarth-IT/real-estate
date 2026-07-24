<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rental_property_archives')) {
            return;
        }

        if (! Schema::hasColumn('rental_property_archives', 'floor_plan_type')) {
            Schema::table('rental_property_archives', function (Blueprint $table) {
                $table->string('floor_plan_type', 20)->nullable()->after('floor_plan_rooms')->comment('間取りタイプ');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rental_property_archives') && Schema::hasColumn('rental_property_archives', 'floor_plan_type')) {
            Schema::table('rental_property_archives', function (Blueprint $table) {
                $table->dropColumn('floor_plan_type');
            });
        }
    }
};
