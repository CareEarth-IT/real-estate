<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('applications')) {
            return;
        }

        if (! Schema::hasColumn('applications', 'screening_ok_at')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->timestamp('screening_ok_at')->nullable()->after('screening_ok')->comment('審査ＯＫ操作日時');
            });
        }

        if (Schema::hasTable('flow_managements') && Schema::hasColumn('flow_managements', 'application_id')) {
            DB::statement('
                UPDATE applications a
                INNER JOIN flow_managements fm ON fm.application_id = a.id
                SET a.screening_ok_at = fm.created_at
                WHERE a.screening_ok = 1
                  AND a.screening_ok_at IS NULL
                  AND fm.created_at IS NOT NULL
            ');
        }

        DB::table('applications')
            ->where('screening_ok', true)
            ->whereNull('screening_ok_at')
            ->update([
                'screening_ok_at' => DB::raw('COALESCE(updated_at, created_at, NOW())'),
            ]);
    }

    public function down(): void
    {
        if (Schema::hasTable('applications') && Schema::hasColumn('applications', 'screening_ok_at')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->dropColumn('screening_ok_at');
            });
        }
    }
};
