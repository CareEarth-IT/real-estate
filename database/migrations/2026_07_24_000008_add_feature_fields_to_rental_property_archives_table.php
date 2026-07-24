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
                'management_form' => fn (Blueprint $t) => $t->string('management_form', 20)->nullable()->comment('管理形態'),
                'has_reform_detail' => fn (Blueprint $t) => $t->boolean('has_reform_detail')->default(false)->comment('リフォーム詳細あり'),
                'has_other_transit' => fn (Blueprint $t) => $t->boolean('has_other_transit')->default(false)->comment('他交通機関あり'),
                'energy_performance' => fn (Blueprint $t) => $t->string('energy_performance', 20)->nullable()->comment('エネルギー消費性能'),
                'has_env_facility_distance_1' => fn (Blueprint $t) => $t->boolean('has_env_facility_distance_1')->default(false)->comment('環境設備・距離1'),
                'has_env_facility_distance_2' => fn (Blueprint $t) => $t->boolean('has_env_facility_distance_2')->default(false)->comment('環境設備・距離2'),
                'has_env_facility_adjacent_1' => fn (Blueprint $t) => $t->boolean('has_env_facility_adjacent_1')->default(false)->comment('環境設備・隣接1'),
                'has_env_facility_adjacent_2' => fn (Blueprint $t) => $t->boolean('has_env_facility_adjacent_2')->default(false)->comment('環境設備・隣接2'),
                'has_env_facility_1f' => fn (Blueprint $t) => $t->boolean('has_env_facility_1f')->default(false)->comment('環境設備・1F'),
                'insulation_grade' => fn (Blueprint $t) => $t->unsignedTinyInteger('insulation_grade')->nullable()->comment('断熱性能（1-7）'),
                'utility_cost_major' => fn (Blueprint $t) => $t->unsignedInteger('utility_cost_major')->nullable()->comment('目安光熱費（整数）'),
                'utility_cost_minor' => fn (Blueprint $t) => $t->unsignedTinyInteger('utility_cost_minor')->nullable()->comment('目安光熱費（小数）'),
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
            'management_form',
            'has_reform_detail',
            'has_other_transit',
            'energy_performance',
            'has_env_facility_distance_1',
            'has_env_facility_distance_2',
            'has_env_facility_adjacent_1',
            'has_env_facility_adjacent_2',
            'has_env_facility_1f',
            'insulation_grade',
            'utility_cost_major',
            'utility_cost_minor',
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
