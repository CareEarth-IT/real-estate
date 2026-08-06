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
            if (! Schema::hasColumn('rental_property_archives', 'floor_features')) {
                $table->json('floor_features')->nullable()->comment('階・フロア');
            }
            if (! Schema::hasColumn('rental_property_archives', 'outdoor_space')) {
                $table->json('outdoor_space')->nullable()->comment('居室外スペース');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('rental_property_archives')) {
            return;
        }

        Schema::table('rental_property_archives', function (Blueprint $table) {
            if (Schema::hasColumn('rental_property_archives', 'floor_features')) {
                $table->dropColumn('floor_features');
            }
            if (Schema::hasColumn('rental_property_archives', 'outdoor_space')) {
                $table->dropColumn('outdoor_space');
            }
        });
    }
};
