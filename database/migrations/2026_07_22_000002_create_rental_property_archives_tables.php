<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rental_property_archives')) {
            Schema::create('rental_property_archives', function (Blueprint $table) {
                $table->id();
                $table->string('property_name')->nullable()->comment('物件名');
                $table->string('address')->nullable()->comment('住所');
                $table->string('building_age')->nullable()->comment('築年数');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('rental_property_archive_images')) {
            Schema::create('rental_property_archive_images', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('rental_property_archive_id');
                $table->string('path')->comment('画像パス');
                $table->string('original_name')->nullable()->comment('元ファイル名');
                $table->unsignedInteger('sort_order')->default(0)->comment('表示順');
                $table->timestamps();

                $table->foreign('rental_property_archive_id', 'rpa_images_archive_id_fk')
                    ->references('id')
                    ->on('rental_property_archives')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_property_archive_images');
        Schema::dropIfExists('rental_property_archives');
    }
};
