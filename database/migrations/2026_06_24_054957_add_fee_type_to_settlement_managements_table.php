<?php

use App\Models\FlowManagement;
use App\Models\SettlementManagement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('settlement_managements', 'fee_type')) {
            Schema::table('settlement_managements', function (Blueprint $table) {
                $table->string('fee_type', 20)->nullable()->after('flow_management_id')->comment('手数料種別（advertising / broker_fee）');
            });
        }

        FlowManagement::query()
            ->where('settlement_transition', true)
            ->each(fn (FlowManagement $flowManagement) => SettlementManagement::syncFromFlowManagement($flowManagement));

        $indexExists = DB::selectOne("
            SELECT COUNT(*) AS count
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'settlement_managements'
              AND INDEX_NAME = 'settlement_managements_flow_management_id_fee_type_unique'
        ");

        if ((int) ($indexExists->count ?? 0) === 0 && Schema::hasColumn('settlement_managements', 'fee_type')) {
            Schema::table('settlement_managements', function (Blueprint $table) {
                $table->unique(['flow_management_id', 'fee_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('settlement_managements', function (Blueprint $table) {
            $table->dropUnique(['flow_management_id', 'fee_type']);
            $table->dropColumn('fee_type');
        });
    }
};
