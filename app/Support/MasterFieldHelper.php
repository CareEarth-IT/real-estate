<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\SettlementManagement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class MasterFieldHelper
{
    /**
     * @return list<string>
     */
    public static function readonlyColumns(): array
    {
        return ['id', 'created_at', 'updated_at'];
    }

    public static function isReadonly(string $column): bool
    {
        return in_array($column, self::readonlyColumns(), true) || $column === 'case_number';
    }

    /**
     * @param  list<string>  $columns
     * @param  array<string, string>  $casts
     * @param  array<string, string>  $columnTypes
     * @return array<string, string>
     */
    public static function inputTypesForColumns(array $columns, array $casts, array $columnTypes): array
    {
        $inputTypes = [];

        foreach ($columns as $column) {
            $inputTypes[$column] = self::inputTypeForColumn($column, $casts, $columnTypes);
        }

        return $inputTypes;
    }

    /**
     * @param  array<string, string>  $casts
     * @param  array<string, string>  $columnTypes
     * @return 'readonly'|'checkbox'|'date'|'datetime'|'number'|'textarea'|'select'|'password'|'text'
     */
    public static function inputTypeForColumn(string $column, array $casts, array $columnTypes): string
    {
        if (self::isReadonly($column)) {
            return 'readonly';
        }

        if ($column === 'password') {
            return 'password';
        }

        if ($column === 'fee_type') {
            return 'select';
        }

        $cast = $casts[$column] ?? null;

        if ($cast === 'boolean' || $cast === 'bool') {
            return 'checkbox';
        }

        if ($cast === 'integer' || $cast === 'int') {
            return 'number';
        }

        if ($cast === 'date') {
            return 'date';
        }

        if (in_array($cast, ['datetime', 'immutable_datetime', 'timestamp'], true)) {
            return 'datetime';
        }

        $schemaType = $columnTypes[$column] ?? 'string';

        if ($schemaType === 'text') {
            return 'textarea';
        }

        if (in_array($schemaType, ['integer', 'bigint', 'smallint', 'mediumint'], true)) {
            return 'number';
        }

        if (in_array($schemaType, ['date'], true)) {
            return 'date';
        }

        if (in_array($schemaType, ['datetime', 'timestamp'], true)) {
            return 'datetime';
        }

        if ($schemaType === 'boolean') {
            return 'checkbox';
        }

        if (self::isLongTextColumn($column)) {
            return 'textarea';
        }

        return 'text';
    }

    /**
     * @return 'readonly'|'checkbox'|'date'|'datetime'|'number'|'textarea'|'select'|'password'|'text'
     */
    public static function inputType(Model $record, string $column): string
    {
        $tableKey = self::tableKeyForModel($record);

        if ($tableKey === null) {
            return self::inputTypeForColumn($column, $record->getCasts(), []);
        }

        return MasterTableRegistry::schema($tableKey)['inputTypes'][$column];
    }

    public static function tableKeyForModel(Model $record): ?string
    {
        foreach (MasterTableRegistry::tables() as $key => $config) {
            if ($record instanceof $config['model']) {
                return $key;
            }
        }

        return null;
    }

    public static function isLongTextColumn(string $column): bool
    {
        return in_array($column, [
            'status',
            'memo',
            'remarks',
            'appliance_support_notes',
            'document_deadline',
        ], true);
    }

    /**
     * @return array<int, mixed>
     */
    public static function validationRulesForInputType(string $inputType): array
    {
        return match ($inputType) {
            'checkbox' => ['nullable', 'boolean'],
            'number' => ['nullable', 'integer'],
            'date' => ['nullable', 'date'],
            'datetime' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'max:255'],
            'select' => ['nullable', 'string', Rule::in([
                SettlementManagement::FEE_TYPE_ADVERTISING,
                SettlementManagement::FEE_TYPE_BROKER,
            ])],
            'textarea' => ['nullable', 'string', 'max:65535'],
            default => ['nullable', 'string', 'max:2048'],
        };
    }

    /**
     * @return array<int, mixed>
     */
    public static function validationRules(Model $record, string $column): array
    {
        if (self::isReadonly($column)) {
            return ['prohibited'];
        }

        return self::validationRulesForInputType(self::inputType($record, $column));
    }

    public static function formatValueForInput(Model $record, string $column, ?string $inputType = null): string
    {
        $value = $record->getAttribute($column);

        if ($value === null) {
            return '';
        }

        $inputType ??= self::inputType($record, $column);

        if ($inputType === 'date' && $value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if ($inputType === 'datetime' && $value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d\TH:i');
        }

        if ($inputType === 'password') {
            return '';
        }

        if ($inputType === 'checkbox') {
            return $value ? '1' : '0';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    public static function formatValueForDisplay(Model $record, string $column): string
    {
        $value = $record->getAttribute($column);

        if ($value === null) {
            return '—';
        }

        if ($column === 'password') {
            return '（設定済み）';
        }

        if ($column === 'remember_token' && $value !== '') {
            return '（設定済み）';
        }

        if ($column === 'fee_type') {
            return match ($value) {
                SettlementManagement::FEE_TYPE_ADVERTISING => '広告料',
                SettlementManagement::FEE_TYPE_BROKER => '仲介手数料',
                default => (string) $value,
            };
        }

        if ($column === 'case_number') {
            return Customer::formatDisplayId((int) $value) ?? '—';
        }

        if ($column === 'has_broker_fee') {
            if ($value === null) {
                return '未定';
            }

            return $value ? 'あり' : 'なし';
        }

        if (is_bool($value)) {
            return $value ? 'あり' : 'なし';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y/m/d H:i');
        }

        return (string) $value;
    }

    /**
     * @return array<string, string>
     */
    public static function selectOptions(string $column): array
    {
        return match ($column) {
            'fee_type' => [
                '' => '（未設定）',
                SettlementManagement::FEE_TYPE_ADVERTISING => '広告料',
                SettlementManagement::FEE_TYPE_BROKER => '仲介手数料',
            ],
            default => [],
        };
    }

    public static function normalizeValue(string $inputType, mixed $value): mixed
    {
        if ($value === '') {
            return null;
        }

        if ($inputType === 'checkbox') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        if ($inputType === 'number' && $value !== null) {
            return (int) $value;
        }

        return $value;
    }
}
