<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('flow_managements')) {
            return;
        }

        if (! Schema::hasColumn('flow_managements', 'contract_doc_extra_links')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->json('contract_doc_extra_links')->nullable()->comment('契約書類・追加リンク');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('flow_managements') && Schema::hasColumn('flow_managements', 'contract_doc_extra_links')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->dropColumn('contract_doc_extra_links');
            });
        }
    }
};
