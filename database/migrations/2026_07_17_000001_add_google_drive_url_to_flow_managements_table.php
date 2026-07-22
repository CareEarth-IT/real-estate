<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('flow_managements') && ! Schema::hasColumn('flow_managements', 'google_drive_url')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->text('google_drive_url')->nullable()->comment('Google Driveリンク');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('flow_managements') && Schema::hasColumn('flow_managements', 'google_drive_url')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->dropColumn('google_drive_url');
            });
        }
    }
};
