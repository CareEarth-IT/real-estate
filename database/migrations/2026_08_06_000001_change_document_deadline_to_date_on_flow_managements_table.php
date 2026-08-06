<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('flow_managements') || ! Schema::hasColumn('flow_managements', 'document_deadline')) {
            return;
        }

        $rows = DB::table('flow_managements')
            ->select('id', 'document_deadline')
            ->whereNotNull('document_deadline')
            ->where('document_deadline', '!=', '')
            ->get();

        foreach ($rows as $row) {
            $normalized = $this->normalizeDate((string) $row->document_deadline);
            DB::table('flow_managements')
                ->where('id', $row->id)
                ->update(['document_deadline' => $normalized]);
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE flow_managements MODIFY document_deadline DATE NULL COMMENT "書類期日"');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('flow_managements') || ! Schema::hasColumn('flow_managements', 'document_deadline')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE flow_managements MODIFY document_deadline TEXT NULL COMMENT "書類期日"');
        }
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $value = str_replace(['年', '月', '日', '.'], ['/', '/', '', '/'], $value);
        $value = preg_replace('/\s+/', '', $value) ?? $value;

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
};
