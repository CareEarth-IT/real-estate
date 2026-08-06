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
            if (! Schema::hasColumn('rental_property_archives', 'common_area')) {
                $table->json('common_area')->nullable()->comment('共用部');
            }
            if (! Schema::hasColumn('rental_property_archives', 'size_features')) {
                $table->json('size_features')->nullable()->comment('広さ');
            }
            if (! Schema::hasColumn('rental_property_archives', 'building_age_features')) {
                $table->json('building_age_features')->nullable()->comment('築年数');
            }
            if (! Schema::hasColumn('rental_property_archives', 'parking_bicycle')) {
                $table->json('parking_bicycle')->nullable()->comment('駐車・駐輪');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('rental_property_archives')) {
            return;
        }

        Schema::table('rental_property_archives', function (Blueprint $table) {
            foreach (['common_area', 'size_features', 'building_age_features', 'parking_bicycle'] as $column) {
                if (Schema::hasColumn('rental_property_archives', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
