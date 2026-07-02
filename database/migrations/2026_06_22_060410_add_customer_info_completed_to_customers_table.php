<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('customers', 'customer_info_completed')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->boolean('customer_info_completed')->default(false)->after('emergency_contact_email')->comment('顧客情報入力済み');
            });
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('customer_info_completed');
        });
    }
};
