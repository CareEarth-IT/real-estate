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

        if (! Schema::hasColumn('rental_property_archives', 'floor_plan_features')) {
            Schema::table('rental_property_archives', function (Blueprint $table) {
                $table->json('floor_plan_features')->nullable()->comment('間取り');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rental_property_archives') && Schema::hasColumn('rental_property_archives', 'floor_plan_features')) {
            Schema::table('rental_property_archives', function (Blueprint $table) {
                $table->dropColumn('floor_plan_features');
            });
        }
    }
};
