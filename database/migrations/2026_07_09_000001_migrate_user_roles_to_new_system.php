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
            ->whereIn('role', ['fudosan', 'keiri'])
            ->update(['role' => 'editor', 'updated_at' => now()]);
    }

    public function down(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('careearth_users')) {
            return;
        }

        DB::table('careearth_users')
            ->where('role', 'bucho')
            ->update(['role' => 'admin', 'updated_at' => now()]);

        DB::table('careearth_users')
            ->where('role', 'editor')
            ->update(['role' => 'fudosan', 'updated_at' => now()]);

        DB::table('careearth_users')
            ->where('role', 'viewer')
            ->update(['role' => 'fudosan', 'updated_at' => now()]);
    }
};
