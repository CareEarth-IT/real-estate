<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE customers
                MODIFY name TEXT NULL,
                MODIFY move_in_date DATE NULL,
                MODIFY contract_period VARCHAR(50) NULL,
                MODIFY contract_period_type TINYINT(1) NULL,
                MODIFY property_name TEXT NULL,
                MODIFY room_number TEXT NULL,
                MODIFY address TEXT NULL,
                MODIFY management_company TEXT NULL,
                MODIFY date_of_birth DATE NULL,
                MODIFY is_married TINYINT(1) NULL,
                MODIFY mobile_number TEXT NULL,
                MODIFY email TEXT NULL,
                MODIFY occupation TEXT NULL,
                MODIFY company_or_school_name TEXT NULL,
                MODIFY company_or_school_phone TEXT NULL,
                MODIFY company_or_school_address TEXT NULL,
                MODIFY emergency_contact_name TEXT NULL,
                MODIFY emergency_contact_relationship TEXT NULL,
                MODIFY emergency_contact_date_of_birth DATE NULL,
                MODIFY emergency_contact_address TEXT NULL,
                MODIFY emergency_contact_mobile TEXT NULL,
                MODIFY emergency_contact_email TEXT NULL
            ');
        }

        if (! Schema::hasColumn('customers', 'case_number')) {
            return;
        }

        $customers = DB::table('customers')
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('id');

        $next = 1;
        foreach ($customers as $customerId) {
            DB::table('customers')
                ->where('id', $customerId)
                ->update(['case_number' => $next++]);
        }
    }

    public function down(): void
    {
        // 採番・NULL許可のロールバックは行わない
    }
};
