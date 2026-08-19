<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settlement_managements')) {
            return;
        }

        Schema::table('settlement_managements', function (Blueprint $table) {
            if (! Schema::hasColumn('settlement_managements', 'business_type')) {
                $table->string('business_type', 100)->nullable()->after('management_number')->comment('事業種別');
            }

            if (! Schema::hasColumn('settlement_managements', 'sales_recorded_month')) {
                $table->unsignedInteger('sales_recorded_month')->nullable()->after('estimated_sales')->comment('売上計上月 (yyyy/mm)');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('settlement_managements')) {
            return;
        }

        Schema::table('settlement_managements', function (Blueprint $table) {
            if (Schema::hasColumn('settlement_managements', 'business_type')) {
                $table->dropColumn('business_type');
            }

            if (Schema::hasColumn('settlement_managements', 'sales_recorded_month')) {
                $table->dropColumn('sales_recorded_month');
            }
        });
    }
};
