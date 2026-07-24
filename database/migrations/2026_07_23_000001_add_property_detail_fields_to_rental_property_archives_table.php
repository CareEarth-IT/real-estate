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
            if (! Schema::hasColumn('rental_property_archives', 'floors_above')) {
                $table->unsignedSmallInteger('floors_above')->nullable()->after('property_name')->comment('地上階数');
            }
            if (! Schema::hasColumn('rental_property_archives', 'floors_below')) {
                $table->unsignedSmallInteger('floors_below')->nullable()->after('floors_above')->comment('地下階数');
            }
            if (! Schema::hasColumn('rental_property_archives', 'floor_part')) {
                $table->unsignedSmallInteger('floor_part')->nullable()->after('floors_below')->comment('階部分');
            }
            if (! Schema::hasColumn('rental_property_archives', 'room_number')) {
                $table->string('room_number', 50)->nullable()->after('floor_part')->comment('号室');
            }
            if (! Schema::hasColumn('rental_property_archives', 'property_type')) {
                $table->string('property_type', 50)->nullable()->after('room_number')->comment('物件種別');
            }
            if (! Schema::hasColumn('rental_property_archives', 'structure')) {
                $table->string('structure', 50)->nullable()->after('property_type')->comment('構造');
            }
            if (! Schema::hasColumn('rental_property_archives', 'built_year')) {
                $table->unsignedSmallInteger('built_year')->nullable()->after('structure')->comment('築年（西暦）');
            }
            if (! Schema::hasColumn('rental_property_archives', 'built_month')) {
                $table->unsignedTinyInteger('built_month')->nullable()->after('built_year')->comment('築月');
            }
            if (! Schema::hasColumn('rental_property_archives', 'building_condition')) {
                $table->string('building_condition', 20)->nullable()->after('built_month')->comment('中古/新築/未入居');
            }
            if (! Schema::hasColumn('rental_property_archives', 'postal_code')) {
                $table->string('postal_code', 16)->nullable()->after('building_condition')->comment('郵便番号');
            }
            if (! Schema::hasColumn('rental_property_archives', 'location')) {
                $table->string('location')->nullable()->after('postal_code')->comment('所在地');
            }
            if (! Schema::hasColumn('rental_property_archives', 'address_detail')) {
                $table->string('address_detail')->nullable()->after('location')->comment('以下住所');
            }
            if (! Schema::hasColumn('rental_property_archives', 'block_building')) {
                $table->string('block_building')->nullable()->after('address_detail')->comment('街区・号棟');
            }
            if (! Schema::hasColumn('rental_property_archives', 'show_on_map')) {
                $table->boolean('show_on_map')->default(false)->after('block_building')->comment('地図表示');
            }
            if (! Schema::hasColumn('rental_property_archives', 'transit1_line')) {
                $table->string('transit1_line')->nullable()->after('show_on_map')->comment('交通1沿線');
            }
            if (! Schema::hasColumn('rental_property_archives', 'transit1_station')) {
                $table->string('transit1_station')->nullable()->after('transit1_line')->comment('交通1駅');
            }
            if (! Schema::hasColumn('rental_property_archives', 'transit1_method')) {
                $table->string('transit1_method', 20)->nullable()->after('transit1_station')->comment('交通1手段');
            }
            if (! Schema::hasColumn('rental_property_archives', 'transit1_minutes')) {
                $table->unsignedSmallInteger('transit1_minutes')->nullable()->after('transit1_method')->comment('交通1分');
            }
            if (! Schema::hasColumn('rental_property_archives', 'transit2_line')) {
                $table->string('transit2_line')->nullable()->after('transit1_minutes')->comment('交通2沿線');
            }
            if (! Schema::hasColumn('rental_property_archives', 'transit2_station')) {
                $table->string('transit2_station')->nullable()->after('transit2_line')->comment('交通2駅');
            }
            if (! Schema::hasColumn('rental_property_archives', 'transit2_method')) {
                $table->string('transit2_method', 20)->nullable()->after('transit2_station')->comment('交通2手段');
            }
            if (! Schema::hasColumn('rental_property_archives', 'transit2_minutes')) {
                $table->unsignedSmallInteger('transit2_minutes')->nullable()->after('transit2_method')->comment('交通2分');
            }
            if (! Schema::hasColumn('rental_property_archives', 'transit3_line')) {
                $table->string('transit3_line')->nullable()->after('transit2_minutes')->comment('交通3沿線');
            }
            if (! Schema::hasColumn('rental_property_archives', 'transit3_station')) {
                $table->string('transit3_station')->nullable()->after('transit3_line')->comment('交通3駅');
            }
            if (! Schema::hasColumn('rental_property_archives', 'transit3_method')) {
                $table->string('transit3_method', 20)->nullable()->after('transit3_station')->comment('交通3手段');
            }
            if (! Schema::hasColumn('rental_property_archives', 'transit3_minutes')) {
                $table->unsignedSmallInteger('transit3_minutes')->nullable()->after('transit3_method')->comment('交通3分');
            }
            if (! Schema::hasColumn('rental_property_archives', 'landlord_name')) {
                $table->string('landlord_name')->nullable()->after('transit3_minutes')->comment('賃主名');
            }
            if (! Schema::hasColumn('rental_property_archives', 'total_units')) {
                $table->string('total_units', 50)->nullable()->after('landlord_name')->comment('総戸数');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('rental_property_archives')) {
            return;
        }

        $columns = [
            'floors_above',
            'floors_below',
            'floor_part',
            'room_number',
            'property_type',
            'structure',
            'built_year',
            'built_month',
            'building_condition',
            'postal_code',
            'location',
            'address_detail',
            'block_building',
            'show_on_map',
            'transit1_line',
            'transit1_station',
            'transit1_method',
            'transit1_minutes',
            'transit2_line',
            'transit2_station',
            'transit2_method',
            'transit2_minutes',
            'transit3_line',
            'transit3_station',
            'transit3_method',
            'transit3_minutes',
            'landlord_name',
            'total_units',
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
