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
            $columns = [
                'contract_doc_resident_record_confirmed' => '契約書類：住民票 確認完了',
                'contract_doc_residence_card_confirmed' => '契約書類：在留カード 確認完了',
                'contract_doc_passport_confirmed' => '契約書類：パスポート 確認完了',
                'contract_doc_payslip_confirmed' => '契約書類：給与明細 確認完了',
                'contract_doc_face_photo_confirmed' => '契約書類：顔写真 確認完了',
                'contract_doc_identity_verification_confirmed' => '契約書類：本人確認 確認完了',
            ];

            foreach ($columns as $column => $comment) {
                if (! Schema::hasColumn('flow_managements', $column)) {
                    $table->boolean($column)->default(false)->comment($comment);
                }
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
                'contract_doc_resident_record_confirmed',
                'contract_doc_residence_card_confirmed',
                'contract_doc_passport_confirmed',
                'contract_doc_payslip_confirmed',
                'contract_doc_face_photo_confirmed',
                'contract_doc_identity_verification_confirmed',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('flow_managements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
