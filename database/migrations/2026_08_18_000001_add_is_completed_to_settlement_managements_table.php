<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settlement_managements') || Schema::hasColumn('settlement_managements', 'is_completed')) {
            return;
        }

        Schema::table('settlement_managements', function (Blueprint $table) {
            $table->boolean('is_completed')->default(false)->after('individual_invoice_printing')->comment('振込完了');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('settlement_managements') || ! Schema::hasColumn('settlement_managements', 'is_completed')) {
            return;
        }

        Schema::table('settlement_managements', function (Blueprint $table) {
            $table->dropColumn('is_completed');
        });
    }
};
