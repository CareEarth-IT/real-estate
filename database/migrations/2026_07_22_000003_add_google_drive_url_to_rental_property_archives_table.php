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

        if (! Schema::hasColumn('rental_property_archives', 'google_drive_url')) {
            Schema::table('rental_property_archives', function (Blueprint $table) {
                $table->text('google_drive_url')->nullable()->after('building_age')->comment('Googleドライブリンク');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rental_property_archives') && Schema::hasColumn('rental_property_archives', 'google_drive_url')) {
            Schema::table('rental_property_archives', function (Blueprint $table) {
                $table->dropColumn('google_drive_url');
            });
        }
    }
};
