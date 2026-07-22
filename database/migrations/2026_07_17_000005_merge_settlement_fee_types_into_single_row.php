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
            if (! Schema::hasColumn('settlement_managements', 'advertising_fee_amount')) {
                $table->integer('advertising_fee_amount')->nullable()->after('estimated_sales')->comment('広告料');
            }
            if (! Schema::hasColumn('settlement_managements', 'broker_fee_amount')) {
                $table->integer('broker_fee_amount')->nullable()->after('advertising_fee_amount')->comment('仲介手数料');
            }
        });

        $flowIds = DB::table('settlement_managements')
            ->whereNotNull('flow_management_id')
            ->distinct()
            ->pluck('flow_management_id');

        foreach ($flowIds as $flowId) {
            $rows = DB::table('settlement_managements')
                ->where('flow_management_id', $flowId)
                ->orderBy('id')
                ->get();

            if ($rows->isEmpty()) {
                continue;
            }

            $primary = $rows->first();
            $advertisingAmount = null;
            $brokerAmount = null;
            $types = [];

            foreach ($rows as $row) {
                if ($row->fee_type === 'advertising') {
                    $advertisingAmount = $row->estimated_sales;
                    $types[] = 'advertising';
                } elseif ($row->fee_type === 'broker_fee') {
                    $brokerAmount = $row->estimated_sales;
                    $types[] = 'broker_fee';
                } elseif ($row->fee_type === 'combined') {
                    $types[] = 'advertising';
                    $types[] = 'broker_fee';
                    if (Schema::hasColumn('settlement_managements', 'advertising_fee_amount')) {
                        $advertisingAmount = $row->advertising_fee_amount ?? $advertisingAmount;
                        $brokerAmount = $row->broker_fee_amount ?? $brokerAmount;
                    }
                } elseif ($row->fee_type === null && $advertisingAmount === null && $brokerAmount === null) {
                    $advertisingAmount = $row->estimated_sales;
                }

                if ($row->id === $primary->id) {
                    continue;
                }

                foreach ([
                    'management_number',
                    'contract_date',
                    'settlement_transfer_date',
                    'earned_points',
                    'remarks',
                ] as $field) {
                    if (blank($primary->{$field}) && filled($row->{$field})) {
                        $primary->{$field} = $row->{$field};
                    }
                }

                foreach ([
                    'sales_including_tax',
                    'sales_excluding_tax',
                ] as $field) {
                    if ($primary->{$field} === null && $row->{$field} !== null) {
                        $primary->{$field} = $row->{$field};
                    }
                }

                foreach ([
                    'settlement_transfer_request',
                    'ad_transfer_invoice_creation',
                    'offset_statement_printing',
                    'individual_invoice_printing',
                ] as $field) {
                    if ((int) $row->{$field} === 1) {
                        $primary->{$field} = 1;
                    }
                }
            }

            $types = array_values(array_unique($types));
            $feeType = match (true) {
                count($types) > 1 => 'combined',
                count($types) === 1 => $types[0],
                default => $primary->fee_type,
            };

            $estimatedSales = null;
            if ($advertisingAmount !== null || $brokerAmount !== null) {
                $estimatedSales = (int) ($advertisingAmount ?? 0) + (int) ($brokerAmount ?? 0);
            }

            DB::table('settlement_managements')
                ->where('id', $primary->id)
                ->update([
                    'fee_type' => $feeType,
                    'advertising_fee_amount' => $advertisingAmount,
                    'broker_fee_amount' => $brokerAmount,
                    'estimated_sales' => $estimatedSales ?? $primary->estimated_sales,
                    'management_number' => $primary->management_number,
                    'contract_date' => $primary->contract_date,
                    'settlement_transfer_date' => $primary->settlement_transfer_date,
                    'earned_points' => $primary->earned_points,
                    'remarks' => $primary->remarks,
                    'sales_including_tax' => $primary->sales_including_tax,
                    'sales_excluding_tax' => $primary->sales_excluding_tax,
                    'settlement_transfer_request' => $primary->settlement_transfer_request,
                    'ad_transfer_invoice_creation' => $primary->ad_transfer_invoice_creation,
                    'offset_statement_printing' => $primary->offset_statement_printing,
                    'individual_invoice_printing' => $primary->individual_invoice_printing,
                ]);

            DB::table('settlement_managements')
                ->where('flow_management_id', $flowId)
                ->where('id', '!=', $primary->id)
                ->delete();
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('settlement_managements')) {
            return;
        }

        Schema::table('settlement_managements', function (Blueprint $table) {
            if (Schema::hasColumn('settlement_managements', 'broker_fee_amount')) {
                $table->dropColumn('broker_fee_amount');
            }
            if (Schema::hasColumn('settlement_managements', 'advertising_fee_amount')) {
                $table->dropColumn('advertising_fee_amount');
            }
        });
    }
};
