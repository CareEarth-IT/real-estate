<?php

use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers') || ! Schema::hasColumn('customers', 'case_number')) {
            return;
        }

        $customers = DB::table('customers')
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('id');

        $nextCaseNumber = Customer::DISPLAY_ID_START;

        foreach ($customers as $customerId) {
            DB::table('customers')
                ->where('id', $customerId)
                ->update(['case_number' => $nextCaseNumber++]);
        }
    }

    public function down(): void
    {
        // 採番ルール変更のためロールバック不可
    }
};
