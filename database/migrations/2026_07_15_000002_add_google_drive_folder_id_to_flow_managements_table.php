<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flow_managements', function (Blueprint $table) {
            $table->string('google_drive_folder_id', 128)->nullable()->after('contractor');
        });
    }

    public function down(): void
    {
        Schema::table('flow_managements', function (Blueprint $table) {
            $table->dropColumn('google_drive_folder_id');
        });
    }
};
