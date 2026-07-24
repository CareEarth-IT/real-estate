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
                'floor_plan_rooms' => fn (Blueprint $t) => $t->unsignedSmallInteger('floor_plan_rooms')->nullable()->comment('間取り'),
                'area_major' => fn (Blueprint $t) => $t->unsignedInteger('area_major')->nullable()->comment('面積（整数）'),
                'area_minor' => fn (Blueprint $t) => $t->unsignedTinyInteger('area_minor')->nullable()->comment('面積（小数）'),
                'balcony_area_major' => fn (Blueprint $t) => $t->unsignedInteger('balcony_area_major')->nullable()->comment('バルコニー面積（整数）'),
                'balcony_area_minor' => fn (Blueprint $t) => $t->unsignedTinyInteger('balcony_area_minor')->nullable()->comment('バルコニー面積（小数）'),
                'opening_direction' => fn (Blueprint $t) => $t->string('opening_direction', 20)->nullable()->comment('開口向き'),
                'washitsu_tatami' => fn (Blueprint $t) => $t->json('washitsu_tatami')->nullable()->comment('和室畳数'),
                'yoshitsu_tatami' => fn (Blueprint $t) => $t->json('yoshitsu_tatami')->nullable()->comment('洋室畳数'),
                'ldk_detail' => fn (Blueprint $t) => $t->string('ldk_detail')->nullable()->comment('LDK詳細'),
                'nando_sizes' => fn (Blueprint $t) => $t->json('nando_sizes')->nullable()->comment('納戸'),
                'loft_sizes' => fn (Blueprint $t) => $t->json('loft_sizes')->nullable()->comment('ロフト'),
                'study_sizes' => fn (Blueprint $t) => $t->json('study_sizes')->nullable()->comment('書斎'),
                'sunroom_sizes' => fn (Blueprint $t) => $t->json('sunroom_sizes')->nullable()->comment('サンルーム'),
                'grenier_sizes' => fn (Blueprint $t) => $t->json('grenier_sizes')->nullable()->comment('グルニエ'),
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
            'floor_plan_rooms',
            'area_major',
            'area_minor',
            'balcony_area_major',
            'balcony_area_minor',
            'opening_direction',
            'washitsu_tatami',
            'yoshitsu_tatami',
            'ldk_detail',
            'nando_sizes',
            'loft_sizes',
            'study_sizes',
            'sunroom_sizes',
            'grenier_sizes',
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
