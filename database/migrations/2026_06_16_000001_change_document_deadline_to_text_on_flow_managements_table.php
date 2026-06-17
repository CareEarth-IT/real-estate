<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE flow_managements MODIFY document_deadline TEXT NULL COMMENT "書類期日"');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE flow_managements MODIFY document_deadline DATE NULL COMMENT "書類期日"');
    }
};
