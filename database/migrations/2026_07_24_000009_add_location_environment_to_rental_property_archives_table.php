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

        if (! Schema::hasColumn('rental_property_archives', 'location_environment')) {
            Schema::table('rental_property_archives', function (Blueprint $table) {
                $table->json('location_environment')->nullable()->comment('立地・環境');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rental_property_archives') && Schema::hasColumn('rental_property_archives', 'location_environment')) {
            Schema::table('rental_property_archives', function (Blueprint $table) {
                $table->dropColumn('location_environment');
            });
        }
    }
};
