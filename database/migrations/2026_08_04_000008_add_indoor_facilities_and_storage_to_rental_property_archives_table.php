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

        Schema::table('rental_property_archives', function (Blueprint $table) {
            if (! Schema::hasColumn('rental_property_archives', 'indoor_facilities')) {
                $table->json('indoor_facilities')->nullable()->comment('室内設備・仕様');
            }
            if (! Schema::hasColumn('rental_property_archives', 'storage')) {
                $table->json('storage')->nullable()->comment('収納');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('rental_property_archives')) {
            return;
        }

        Schema::table('rental_property_archives', function (Blueprint $table) {
            foreach (['indoor_facilities', 'storage'] as $column) {
                if (Schema::hasColumn('rental_property_archives', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
