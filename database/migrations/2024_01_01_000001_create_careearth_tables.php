<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('property_master')) {
            Schema::create('property_master', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('created_at')->useCurrent();
            $table->string('buyer_name', 255)->comment('購入者');
            $table->string('broker_name', 255)->nullable()->comment('仲介業者名');
            $table->string('owner_name', 255)->nullable()->comment('オーナー名');
            $table->string('property_address', 500)->comment('物件住所');
            $table->unsignedInteger('building_price')->default(0)->comment('建物取得価格');
            $table->unsignedInteger('land_price')->default(0)->comment('土地取得価格');
            $table->string('price_mode', 10)->default('split')->comment('split|total');
            $table->unsignedInteger('total_price')->default(0)->comment('物件価格(合算)');
            $table->unsignedInteger('registration_fee')->default(0)->comment('登記費用');
            $table->unsignedInteger('brokerage_fee')->default(0)->comment('仲介手数料');
            $table->unsignedInteger('property_tax')->default(0)->comment('固定資産税');
            $table->string('sales_person', 255)->nullable()->comment('担当営業');
            $table->string('purchase_certificate', 500)->nullable()->comment('買付証明書');
            $table->string('seal_certificate', 500)->nullable()->comment('印鑑証明書');
            $table->string('registry_certificate', 500)->nullable()->comment('登記事項証明書');
            $table->string('property_registry', 500)->nullable()->comment('不動産登記謄本');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('created_at', 'idx_created_at');
            $table->index('property_address', 'idx_property_address');
            });
        }

        if (! Schema::hasTable('property_addresses')) {
            Schema::create('property_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('address', 500)->unique();
            $table->dateTime('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('sales_persons')) {
            Schema::create('sales_persons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->unique();
            $table->dateTime('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_persons');
        Schema::dropIfExists('property_addresses');
        Schema::dropIfExists('property_master');
    }
};
