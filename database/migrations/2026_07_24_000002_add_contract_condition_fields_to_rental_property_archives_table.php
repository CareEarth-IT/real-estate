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
                'condition_corporation' => fn (Blueprint $t) => $t->string('condition_corporation', 20)->nullable()->comment('法人'),
                'condition_student' => fn (Blueprint $t) => $t->string('condition_student', 20)->nullable()->comment('学生'),
                'condition_gender' => fn (Blueprint $t) => $t->string('condition_gender', 20)->nullable()->comment('性別'),
                'condition_single' => fn (Blueprint $t) => $t->string('condition_single', 20)->nullable()->comment('単身者'),
                'condition_two_tenants' => fn (Blueprint $t) => $t->string('condition_two_tenants', 20)->nullable()->comment('二人入居'),
                'condition_children' => fn (Blueprint $t) => $t->string('condition_children', 20)->nullable()->comment('子供'),
                'condition_pets' => fn (Blueprint $t) => $t->string('condition_pets', 20)->nullable()->comment('ペット'),
                'condition_instruments' => fn (Blueprint $t) => $t->string('condition_instruments', 20)->nullable()->comment('楽器'),
                'condition_office_use' => fn (Blueprint $t) => $t->string('condition_office_use', 20)->nullable()->comment('事務所利用'),
                'condition_roomshare' => fn (Blueprint $t) => $t->string('condition_roomshare', 20)->nullable()->comment('ルームシェア'),
                'has_free_rent' => fn (Blueprint $t) => $t->boolean('has_free_rent')->default(false)->comment('フリーレントあり'),
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
            'condition_corporation',
            'condition_student',
            'condition_gender',
            'condition_single',
            'condition_two_tenants',
            'condition_children',
            'condition_pets',
            'condition_instruments',
            'condition_office_use',
            'condition_roomshare',
            'has_free_rent',
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
