<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('careearth_users')) {
            return;
        }

        DB::table('careearth_users')
            ->where('role', 'bucho')
            ->update(['role' => 'admin', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // 部長ロールは廃止したため、管理者へ戻したユーザーを復元しない。
    }
};
