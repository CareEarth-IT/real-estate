<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Customer;
use App\Models\FlowManagement;
use App\Models\SettlementManagement;
use App\Support\SettlementCsvImportMapper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class SettlementCsvImportService
{
    public function __construct(
        private readonly SettlementCsvImportMapper $mapper = new SettlementCsvImportMapper,
    ) {}

    /**
     * @return array{created: int, updated: int, skipped: int, errors: list<string>}
     */
    public function importUploadedFile(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        if ($path === false) {
            throw new RuntimeException('CSVファイルを読み込めませんでした。');
        }

        return $this->importPath($path);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{created: int, updated: int, skipped: int, errors: list<string>}
     */
    public function importRows(array $rows, string $duplicateMode = 'update'): array
    {
        $duplicateMode = $duplicateMode === 'skip' ? 'skip' : 'update';
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $row) {
            try {
                $existing = $this->findExistingSettlement($row);
                if ($existing !== null && $duplicateMode === 'skip') {
                    $skipped++;
                    continue;
                }

                if ($existing === null && $this->stringValue($row['management_number'] ?? null) === null) {
                    $skipped++;
                    if (count($errors) < 20) {
                        $errors[] = sprintf('%d行目: 新規登録には管理番号または請求書No.が必要です。', $row['line'] ?? 0);
                    }
                    continue;
                }

                DB::transaction(fn () => $this->upsertRow($row));
                if ($existing !== null) {
                    $updated++;
                } else {
                    $created++;
                }
            } catch (Throwable $exception) {
                $skipped++;
                if (count($errors) < 20) {
                    $errors[] = sprintf('%d行目: %s', $row['line'] ?? 0, $exception->getMessage());
                }
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * @return array{created: int, updated: int, skipped: int, errors: list<string>}
     */
    public function importPath(string $path, ?array $columnMap = null, bool $importFromFirstRow = false, string $duplicateMode = 'update'): array
    {
        if ($columnMap === null) {
            $rows = $this->mapper->mapFile($path);
        } else {
            $table = $this->mapper->parseFile($path);
            $rows = $this->mapper->mapParsedRows($table['rows'], $columnMap, $importFromFirstRow);
        }

        return $this->importRows($rows, $duplicateMode);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertRow(array $row): void
    {
        $managementNumber = $this->stringValue($row['management_number'] ?? null);
        $settlement = $this->findExistingSettlement($row);
        $flow = $settlement?->flowManagement;
        $application = $flow?->application;
        $customer = $this->resolveCustomer($row, $settlement, $application, $flow);

        if ($application === null) {
            $application = new Application([
                'screening_ok' => true,
                'screening_ok_at' => now(),
                'is_cancelled' => false,
                'sales_action_required' => false,
            ]);
        }

        $application->fill([
            'customer_id' => $customer?->id,
            'staff_in_charge' => $this->prefer($row['staff_in_charge'] ?? null, $application->staff_in_charge),
            'contractor' => $this->prefer($row['contractor'] ?? null, $application->contractor),
            'property_name' => $this->prefer($row['property_name'] ?? null, $application->property_name),
            'screening_ok' => true,
            'is_cancelled' => false,
        ]);
        if ($application->screening_ok_at === null) {
            $application->screening_ok_at = now();
        }
        $application->save();

        if ($flow === null) {
            $flow = new FlowManagement([
                'application_id' => $application->id,
                'flow_management_transition' => true,
                'settlement_transition' => true,
            ]);
        }

        $flow->fill([
            'application_id' => $application->id,
            'customer_id' => $customer?->id,
            'staff_in_charge' => $this->prefer($row['staff_in_charge'] ?? null, $flow->staff_in_charge),
            'contractor' => $this->prefer($row['contractor'] ?? null, $flow->contractor),
            'property_name' => $this->prefer($row['property_name'] ?? null, $flow->property_name),
            'memo' => $this->prefer($row['remarks'] ?? null, $flow->memo),
            'flow_management_transition' => true,
            'settlement_transition' => true,
        ]);
        $flow->save();

        if ($settlement === null) {
            $settlement = new SettlementManagement([
                'flow_management_id' => $flow->id,
            ]);
        }

        $includingTax = $this->preferInt($row['sales_including_tax'] ?? null, $settlement->sales_including_tax);
        $excludingTax = $this->preferInt($row['sales_excluding_tax'] ?? null, $settlement->sales_excluding_tax);

        $settlement->fill([
            'customer_id' => $customer?->id,
            'flow_management_id' => $flow->id,
            'management_number' => $this->prefer($managementNumber, $settlement->management_number),
            'business_type' => $this->prefer($row['business_type'] ?? null, $settlement->business_type),
            'staff_in_charge' => $this->prefer($row['staff_in_charge'] ?? null, $settlement->staff_in_charge),
            'contractor' => $this->prefer($row['contractor'] ?? null, $settlement->contractor),
            'property_name' => $this->prefer($row['property_name'] ?? null, $settlement->property_name),
            'contract_date' => $this->prefer($row['contract_date'] ?? null, $settlement->contract_date?->toDateString()),
            'sales_recorded_month' => $this->preferInt($row['sales_recorded_month'] ?? null, $settlement->sales_recorded_month),
            'sales_including_tax' => $includingTax,
            'sales_excluding_tax' => $excludingTax,
            'estimated_sales' => $includingTax ?? $excludingTax ?? $settlement->estimated_sales,
            'earned_points' => $this->prefer($row['earned_points'] ?? null, $settlement->earned_points),
            'remarks' => $this->prefer($row['remarks'] ?? null, $settlement->remarks),
        ]);
        $settlement->save();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function findExistingSettlement(array $row): ?SettlementManagement
    {
        $managementNumber = $this->stringValue($row['management_number'] ?? null);
        if ($managementNumber === null) {
            return null;
        }

        return SettlementManagement::query()
            ->with(['flowManagement.application', 'customer'])
            ->where('management_number', $managementNumber)
            ->orderBy('id')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveCustomer(
        array $row,
        ?SettlementManagement $settlement,
        ?Application $application,
        ?FlowManagement $flow,
    ): ?Customer {
        $customer = $settlement?->customer ?? $application?->customer ?? $flow?->customer;
        if ($customer !== null) {
            $this->fillCustomer($customer, $row);
            $customer->save();

            return $customer;
        }

        $caseNumber = $this->caseNumberFromManagementNumber($this->stringValue($row['management_number'] ?? null));
        if ($caseNumber === null) {
            return $customer;
        }

        if ($customer === null) {
            $customer = Customer::query()->where('case_number', $caseNumber)->first();
        }

        if ($customer === null) {
            $customer = new Customer;
            $customer->case_number = $caseNumber;
        }

        $this->fillCustomer($customer, $row);
        $customer->save();

        return $customer;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function fillCustomer(Customer $customer, array $row): void
    {
        $customer->name = $this->prefer($row['contractor'] ?? null, $customer->name) ?? $customer->name;
        $customer->property_name = $this->prefer($row['property_name'] ?? null, $customer->property_name) ?? $customer->property_name;
        if ($customer->name === null || $customer->name === '') {
            $customer->name = $this->stringValue($row['property_name'] ?? null) ?? '未設定';
        }
    }

    private function caseNumberFromManagementNumber(?string $managementNumber): ?int
    {
        if ($managementNumber === null) {
            return null;
        }

        if (preg_match('/^\d{1,10}$/', $managementNumber) !== 1) {
            return null;
        }

        return (int) $managementNumber;
    }

    private function prefer(mixed $incoming, mixed $existing): mixed
    {
        $incoming = $this->stringValue($incoming);

        return $incoming ?? $existing;
    }

    private function preferInt(mixed $incoming, mixed $existing): mixed
    {
        if ($incoming === null || $incoming === '') {
            return $existing;
        }

        return (int) $incoming;
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
