<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->convertTable('applications');
        $this->convertTable('flow_managements');
    }

    public function down(): void
    {
        $this->revertTable('applications');
        $this->revertTable('flow_managements');
    }

    private function convertTable(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'overseas_screening')) {
            return;
        }

        if (! Schema::hasColumn($table, 'japan_stay_schedule')) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dateTime('japan_stay_schedule')->nullable()->after('overseas_screening')->comment('在日日程');
            });
        }

        // Already converted to boolean on a previous partial run.
        $columnType = Schema::getColumnType($table, 'overseas_screening');
        if (in_array($columnType, ['boolean', 'tinyint', 'bit'], true)) {
            return;
        }

        $flaggedIds = DB::table($table)
            ->whereNotNull('overseas_screening')
            ->where('overseas_screening', '!=', '')
            ->pluck('id');

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropColumn('overseas_screening');
        });

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->boolean('overseas_screening')->default(false)->after('contractor_english_name')->comment('海外審査');
        });

        if ($flaggedIds->isNotEmpty()) {
            DB::table($table)->whereIn('id', $flaggedIds)->update(['overseas_screening' => true]);
        }
    }

    private function revertTable(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'overseas_screening')) {
            return;
        }

        $flaggedIds = DB::table($table)
            ->where('overseas_screening', true)
            ->pluck('id');

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropColumn('overseas_screening');
        });

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->text('overseas_screening')->nullable()->after('contractor_english_name')->comment('海外審査');
        });

        if ($flaggedIds->isNotEmpty()) {
            DB::table($table)->whereIn('id', $flaggedIds)->update(['overseas_screening' => '海外審査あり']);
        }

        if (Schema::hasColumn($table, 'japan_stay_schedule')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('japan_stay_schedule');
            });
        }
    }
};
