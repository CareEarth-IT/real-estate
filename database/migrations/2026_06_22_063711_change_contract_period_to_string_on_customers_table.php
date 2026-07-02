<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columnType = DB::selectOne("
            SELECT DATA_TYPE AS type
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'customers'
              AND COLUMN_NAME = 'contract_period'
        ");

        if ($columnType && strtolower((string) $columnType->type) === 'date') {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('contract_period', 50)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->date('contract_period')->change();
        });
    }
};
