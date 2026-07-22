<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('careearth_users')) {
            return;
        }

        if (! Schema::hasColumn('careearth_users', 'name')) {
            Schema::table('careearth_users', function (Blueprint $table) {
                $table->string('name')->nullable()->after('id')->comment('表示名');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('careearth_users') && Schema::hasColumn('careearth_users', 'name')) {
            Schema::table('careearth_users', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
};
