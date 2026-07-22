<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('applications') && ! Schema::hasColumn('applications', 'contractor')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->text('contractor')->nullable()->after('staff_in_charge')->comment('契約者');
            });
        }

        if (Schema::hasTable('flow_managements') && ! Schema::hasColumn('flow_managements', 'contractor')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->text('contractor')->nullable()->after('staff_in_charge')->comment('契約者');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('flow_managements') && Schema::hasColumn('flow_managements', 'contractor')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->dropColumn('contractor');
            });
        }

        if (Schema::hasTable('applications') && Schema::hasColumn('applications', 'contractor')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->dropColumn('contractor');
            });
        }
    }
};
