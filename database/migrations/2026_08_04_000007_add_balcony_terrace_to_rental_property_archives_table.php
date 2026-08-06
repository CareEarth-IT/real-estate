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

        if (! Schema::hasColumn('rental_property_archives', 'balcony_terrace')) {
            Schema::table('rental_property_archives', function (Blueprint $table) {
                $table->json('balcony_terrace')->nullable()->comment('バルコニー・テラス');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rental_property_archives') && Schema::hasColumn('rental_property_archives', 'balcony_terrace')) {
            Schema::table('rental_property_archives', function (Blueprint $table) {
                $table->dropColumn('balcony_terrace');
            });
        }
    }
};
