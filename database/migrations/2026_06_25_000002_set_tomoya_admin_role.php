<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('careearth_users')
            ->where('email', 'tomoya_hayashi@careearth.info')
            ->update([
                'role' => 'admin',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('careearth_users')
            ->where('email', 'tomoya_hayashi@careearth.info')
            ->update([
                'role' => 'keiri',
                'updated_at' => now(),
            ]);
    }
};
