<?php

namespace App\Support;

use App\Models\Application;
use App\Models\Customer;
use App\Models\FlowManagement;
use App\Models\SettlementManagement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class MasterTableRegistry
{
    /**
     * @var array<string, array{
     *     model: class-string<Model>,
     *     label: string,
     *     table: string,
     *     columns: list<string>,
     *     columnTypes: array<string, string>,
     *     labels: array<string, string>,
     *     inputTypes: array<string, string>,
     *     casts: array<string, string>
     * }>
     */
    private static array $schemaCache = [];

    /**
     * @return array<string, array{model: class-string<Model>, label: string}>
     */
    public static function tables(): array
    {
        return [
            'applications' => [
                'model' => Application::class,
                'label' => '申込（applications）',
            ],
            'flow_managements' => [
                'model' => FlowManagement::class,
                'label' => '書類管理（flow_managements）',
            ],
            'customers' => [
                'model' => Customer::class,
                'label' => '顧客（customers）',
            ],
            'settlement_managements' => [
                'model' => SettlementManagement::class,
                'label' => '決済金管理（settlement_managements）',
            ],
        ];
    }

    public static function has(string $tableKey): bool
    {
        return array_key_exists($tableKey, self::tables());
    }

    /**
     * @return array{model: class-string<Model>, label: string, table: string}
     */
    public static function resolve(string $tableKey): array
    {
        $schema = self::schema($tableKey);

        return [
            'model' => $schema['model'],
            'label' => self::tables()[$tableKey]['label'],
            'table' => $schema['table'],
        ];
    }

    /**
     * @return array{
     *     model: class-string<Model>,
     *     table: string,
     *     columns: list<string>,
     *     columnTypes: array<string, string>,
     *     labels: array<string, string>,
     *     inputTypes: array<string, string>,
     *     casts: array<string, string>
     * }
     */
    public static function schema(string $tableKey): array
    {
        if (isset(self::$schemaCache[$tableKey])) {
            return self::$schemaCache[$tableKey];
        }

        if (! self::has($tableKey)) {
            abort(404);
        }

        $modelClass = self::tables()[$tableKey]['model'];
        /** @var Model $model */
        $model = new $modelClass;
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);

        $columnTypes = [];
        foreach ($columns as $column) {
            $columnTypes[$column] = Schema::getColumnType($table, $column);
        }

        $casts = $model->getCasts();
        $modelLabels = method_exists($modelClass, 'columnLabels')
            ? $modelClass::columnLabels()
            : [];

        $labels = [];
        foreach ($columns as $column) {
            $labels[$column] = $modelLabels[$column] ?? self::defaultLabel($column);
        }

        $inputTypes = MasterFieldHelper::inputTypesForColumns($columns, $casts, $columnTypes);

        return self::$schemaCache[$tableKey] = [
            'model' => $modelClass,
            'table' => $table,
            'columns' => $columns,
            'columnTypes' => $columnTypes,
            'labels' => $labels,
            'inputTypes' => $inputTypes,
            'casts' => $casts,
        ];
    }

    /**
     * @return list<string>
     */
    public static function columns(string $tableKey): array
    {
        return self::schema($tableKey)['columns'];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(string $tableKey): array
    {
        return self::schema($tableKey)['labels'];
    }

    public static function defaultLabel(string $column): string
    {
        return match ($column) {
            'id' => 'ID',
            'created_at' => '作成日時',
            'updated_at' => '更新日時',
            'customer_id' => '顧客ID',
            'application_id' => '申込ID',
            'flow_management_id' => '書類管理ID',
            'fee_type' => '手数料種別',
            'flow_management_transition' => '書類管理移行チェック',
            'customer_info_completed' => '顧客情報入力済み',
            'google_id' => 'Google ID',
            'email_verified_at' => 'メール確認日時',
            'remember_token' => 'リメンバートークン',
            'password' => 'パスワード',
            default => $column,
        };
    }

    public static function applySearch(Builder $query, string $tableKey, string $search): void
    {
        $schema = self::schema($tableKey);
        $table = $schema['table'];
        $like = '%'.addcslashes($search, '%_\\').'%';

        $query->where(function (Builder $nested) use ($schema, $table, $like, $search) {
            foreach ($schema['columns'] as $column) {
                $type = $schema['columnTypes'][$column];

                if (in_array($type, ['string', 'text'], true)) {
                    $nested->orWhere("{$table}.{$column}", 'like', $like);
                } elseif (in_array($type, ['integer', 'bigint', 'smallint', 'mediumint', 'decimal', 'float', 'double'], true)) {
                    $nested->orWhereRaw("CAST({$table}.{$column} AS CHAR) LIKE ?", [$like]);
                } elseif (in_array($type, ['date', 'datetime', 'timestamp'], true)) {
                    $nested->orWhereRaw("DATE_FORMAT({$table}.{$column}, '%Y/%m/%d %H:%i') LIKE ?", [$like]);
                } elseif ($type === 'boolean') {
                    if (str_contains($search, 'あり') || str_contains(strtolower($search), 'true') || $search === '1') {
                        $nested->orWhere("{$table}.{$column}", true);
                    }
                    if (str_contains($search, 'なし') || str_contains(strtolower($search), 'false') || $search === '0') {
                        $nested->orWhere("{$table}.{$column}", false);
                    }
                }
            }
        });
    }
}
