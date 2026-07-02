<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('flow_managements', 'has_broker_fee')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->boolean('has_broker_fee')->default(false)->after('contract_copy_storage')->comment('仲介手数料あり');
            });
        }

        if (Schema::hasColumn('flow_managements', 'application_id')) {
            DB::table('flow_managements')
                ->join('applications', 'flow_managements.application_id', '=', 'applications.id')
                ->where(function ($query) {
                    $query->where('applications.has_broker_fee', true)
                        ->orWhere('applications.broker_fee', '>=', 1);
                })
                ->update(['flow_managements.has_broker_fee' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('flow_managements', function (Blueprint $table) {
            $table->dropColumn('has_broker_fee');
        });
    }
};
