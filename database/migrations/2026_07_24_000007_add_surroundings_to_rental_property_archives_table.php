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

        if (! Schema::hasColumn('rental_property_archives', 'surroundings')) {
            Schema::table('rental_property_archives', function (Blueprint $table) {
                $table->json('surroundings')->nullable()->comment('周辺環境');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rental_property_archives') && Schema::hasColumn('rental_property_archives', 'surroundings')) {
            Schema::table('rental_property_archives', function (Blueprint $table) {
                $table->dropColumn('surroundings');
            });
        }
    }
};
