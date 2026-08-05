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

        if (! Schema::hasColumn('careearth_users', 'employee_id')) {
            Schema::table('careearth_users', function (Blueprint $table) {
                $table->string('employee_id', 50)
                    ->nullable()
                    ->after('email')
                    ->comment('社員ポータルの社員ID');
                $table->unique('employee_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('careearth_users') || ! Schema::hasColumn('careearth_users', 'employee_id')) {
            return;
        }

        Schema::table('careearth_users', function (Blueprint $table) {
            $table->dropUnique(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
};
