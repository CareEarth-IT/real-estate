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

        Schema::table('flow_managements', function (Blueprint $table) {
            if (! Schema::hasColumn('flow_managements', 'contract_doc_resident_record_url')) {
                $table->text('contract_doc_resident_record_url')->nullable()->comment('契約書類：住民票');
            }
            if (! Schema::hasColumn('flow_managements', 'contract_doc_residence_card_url')) {
                $table->text('contract_doc_residence_card_url')->nullable()->comment('契約書類：在留カード（裏表）');
            }
            if (! Schema::hasColumn('flow_managements', 'contract_doc_passport_url')) {
                $table->text('contract_doc_passport_url')->nullable()->comment('契約書類：パスポート');
            }
            if (! Schema::hasColumn('flow_managements', 'contract_doc_payslip_url')) {
                $table->text('contract_doc_payslip_url')->nullable()->comment('契約書類：給与明細（3ヶ月分）');
            }
            if (! Schema::hasColumn('flow_managements', 'contract_doc_face_photo_url')) {
                $table->text('contract_doc_face_photo_url')->nullable()->comment('契約書類：顔写真');
            }
            if (! Schema::hasColumn('flow_managements', 'contract_doc_identity_verification_url')) {
                $table->text('contract_doc_identity_verification_url')->nullable()->comment('契約書類：本人確認（任意）');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('flow_managements')) {
            return;
        }

        Schema::table('flow_managements', function (Blueprint $table) {
            $columns = [
                'contract_doc_resident_record_url',
                'contract_doc_residence_card_url',
                'contract_doc_passport_url',
                'contract_doc_payslip_url',
                'contract_doc_face_photo_url',
                'contract_doc_identity_verification_url',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('flow_managements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
