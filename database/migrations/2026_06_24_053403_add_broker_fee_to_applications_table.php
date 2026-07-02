<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (! Schema::hasColumn('applications', 'has_broker_fee')) {
                $table->boolean('has_broker_fee')->default(false)->after('advertising_fee')->comment('仲介手数料 あり/なし');
            }
            if (! Schema::hasColumn('applications', 'broker_fee')) {
                $table->integer('broker_fee')->nullable()->after('has_broker_fee')->comment('仲介手数料（金額）');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['has_broker_fee', 'broker_fee']);
        });
    }
};
