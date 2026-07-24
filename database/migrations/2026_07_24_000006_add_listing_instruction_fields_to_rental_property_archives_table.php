<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rental_property_archives')) {
            return;
        }

        Schema::table('rental_property_archives', function (Blueprint $table) {
            $columns = [
                'move_in_schedule' => fn (Blueprint $t) => $t->string('move_in_schedule', 20)->nullable()->comment('入居予定'),
                'transaction_type' => fn (Blueprint $t) => $t->string('transaction_type', 50)->nullable()->comment('取引態様'),
                'source_company_name' => fn (Blueprint $t) => $t->string('source_company_name')->nullable()->comment('元付会社名'),
                'source_staff_name' => fn (Blueprint $t) => $t->string('source_staff_name')->nullable()->comment('元付担当者'),
                'source_phone' => fn (Blueprint $t) => $t->string('source_phone', 50)->nullable()->comment('元付電話番号'),
                'source_confirmed_on' => fn (Blueprint $t) => $t->date('source_confirmed_on')->nullable()->comment('元付確認日'),
                'company_property_code' => fn (Blueprint $t) => $t->string('company_property_code')->nullable()->comment('貴社物件コード'),
                'net_listing' => fn (Blueprint $t) => $t->string('net_listing', 50)->nullable()->comment('ネット掲載'),
                'third_party_copy' => fn (Blueprint $t) => $t->string('third_party_copy')->nullable()->comment('他者によるコピー'),
            ];

            foreach ($columns as $name => $definition) {
                if (! Schema::hasColumn('rental_property_archives', $name)) {
                    $definition($table);
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('rental_property_archives')) {
            return;
        }

        $columns = [
            'move_in_schedule',
            'transaction_type',
            'source_company_name',
            'source_staff_name',
            'source_phone',
            'source_confirmed_on',
            'company_property_code',
            'net_listing',
            'third_party_copy',
        ];

        Schema::table('rental_property_archives', function (Blueprint $table) use ($columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn('rental_property_archives', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
