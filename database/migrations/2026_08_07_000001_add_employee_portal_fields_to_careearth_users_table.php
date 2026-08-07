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

        Schema::table('careearth_users', function (Blueprint $table) {
            if (! Schema::hasColumn('careearth_users', 'employee_id')) {
                $table->string('employee_id', 64)->nullable()->unique()->after('email')->comment('社員ポータル社員ID');
            }
            if (! Schema::hasColumn('careearth_users', 'employment_status')) {
                $table->string('employment_status', 50)->nullable()->after('employee_id')->comment('在籍状況');
            }
            if (! Schema::hasColumn('careearth_users', 'synced_at')) {
                $table->timestamp('synced_at')->nullable()->after('employment_status')->comment('社員ポータル最終同期');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('careearth_users')) {
            return;
        }

        Schema::table('careearth_users', function (Blueprint $table) {
            if (Schema::hasColumn('careearth_users', 'synced_at')) {
                $table->dropColumn('synced_at');
            }
            if (Schema::hasColumn('careearth_users', 'employment_status')) {
                $table->dropColumn('employment_status');
            }
            if (Schema::hasColumn('careearth_users', 'employee_id')) {
                $table->dropUnique(['employee_id']);
                $table->dropColumn('employee_id');
            }
        });
    }
};
