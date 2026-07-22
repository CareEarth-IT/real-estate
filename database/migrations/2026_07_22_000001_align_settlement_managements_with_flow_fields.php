<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settlement_managements')) {
            return;
        }

        Schema::table('settlement_managements', function (Blueprint $table) {
            if (! Schema::hasColumn('settlement_managements', 'contractor')) {
                $table->string('contractor')->nullable()->after('staff_in_charge')->comment('契約者');
            }
            if (! Schema::hasColumn('settlement_managements', 'room_number')) {
                $table->string('room_number')->nullable()->after('property_name')->comment('部屋番号');
            }
            if (! Schema::hasColumn('settlement_managements', 'entry_method')) {
                $table->string('entry_method')->nullable()->after('room_number')->comment('記入方法');
            }
        });

        if (Schema::hasTable('flow_managements')) {
            $select = ['id', 'staff_in_charge', 'property_name'];
            foreach (['contractor', 'room_number', 'entry_method'] as $column) {
                if (Schema::hasColumn('flow_managements', $column)) {
                    $select[] = $column;
                }
            }

            $flows = DB::table('flow_managements')->select($select)->get();
            foreach ($flows as $flow) {
                $payload = [
                    'staff_in_charge' => $flow->staff_in_charge,
                    'property_name' => $flow->property_name,
                ];
                if (property_exists($flow, 'contractor')) {
                    $payload['contractor'] = $flow->contractor;
                }
                if (property_exists($flow, 'room_number')) {
                    $payload['room_number'] = $flow->room_number;
                }
                if (property_exists($flow, 'entry_method')) {
                    $payload['entry_method'] = $flow->entry_method;
                }

                DB::table('settlement_managements')
                    ->where('flow_management_id', $flow->id)
                    ->update($payload);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('settlement_managements')) {
            return;
        }

        Schema::table('settlement_managements', function (Blueprint $table) {
            foreach (['entry_method', 'room_number', 'contractor'] as $column) {
                if (Schema::hasColumn('settlement_managements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
