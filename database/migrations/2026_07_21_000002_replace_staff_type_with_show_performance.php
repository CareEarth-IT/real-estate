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

        if (! Schema::hasColumn('careearth_users', 'show_performance')) {
            Schema::table('careearth_users', function (Blueprint $table) {
                $table->boolean('show_performance')
                    ->default(true)
                    ->after('role')
                    ->comment('成績表示（ホームの担当者業績一覧）');
            });
        }

        if (Schema::hasColumn('careearth_users', 'staff_type')) {
            Schema::table('careearth_users', function (Blueprint $table) {
                $table->dropColumn('staff_type');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('careearth_users')) {
            return;
        }

        if (! Schema::hasColumn('careearth_users', 'staff_type')) {
            Schema::table('careearth_users', function (Blueprint $table) {
                $table->string('staff_type', 20)
                    ->default('japanese')
                    ->after('role')
                    ->comment('japanese=日本人スタッフ / foreign=外国人スタッフ');
            });
        }

        if (Schema::hasColumn('careearth_users', 'show_performance')) {
            Schema::table('careearth_users', function (Blueprint $table) {
                $table->dropColumn('show_performance');
            });
        }
    }
};
