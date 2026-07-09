<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Support\MasterFieldHelper;
use App\Support\MasterTableRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(Request $request): View
    {
        $tables = MasterTableRegistry::tables();
        $tableKey = (string) ($request->route('table') ?? $request->query('table', array_key_first($tables)));

        if (! MasterTableRegistry::has($tableKey)) {
            $tableKey = array_key_first($tables);
        }

        $schema = MasterTableRegistry::schema($tableKey);
        $modelClass = $schema['model'];
        $search = trim((string) $request->input('search', ''));

        $query = $modelClass::query();

        if ($tableKey === 'customers') {
            $query->orderBy('case_number');
        } else {
            $query->orderByDesc('id');
        }

        if ($search !== '') {
            MasterTableRegistry::applySearch($query, $tableKey, $search);
        }

        $records = $query
            ->paginate(15)
            ->withQueryString();

        $pageTitle = match ($tableKey) {
            'flow_managements' => '書類管理',
            'customers' => '顧客情報',
            'settlement_managements' => '決済金管理',
            'applications' => '申込',
            default => 'マスター管理',
        };

        $masterTabTitle = match ($tableKey) {
            'flow_managements' => '書類管理',
            'customers' => '顧客',
            'settlement_managements' => '決済金管理',
            'applications' => '申込',
            default => 'マスター管理',
        };

        $hiddenColumns = $this->hiddenColumnsFor($request, $tableKey);
        $columns = array_values(array_filter(
            $schema['columns'],
            fn (string $column) => ! in_array($column, $hiddenColumns, true)
        ));

        return view('master.data.index', [
            'layout' => $request->routeIs('master.*') ? 'layouts.master' : 'layouts.admin',
            'tables' => $tables,
            'tableKey' => $tableKey,
            'tableLabel' => $tables[$tableKey]['label'],
            'pageTitle' => $pageTitle,
            'masterTabTitle' => $masterTabTitle,
            'showTabs' => $request->routeIs('master.*'),
            'columns' => $columns,
            'columnLabels' => $schema['labels'],
            'columnInputTypes' => $schema['inputTypes'],
            'records' => $records,
            'search' => $search,
        ]);
    }

    /**
     * @return list<string>
     */
    private function hiddenColumnsFor(Request $request, string $tableKey): array
    {
        if ($request->routeIs('admin.customers.index') && $tableKey === 'customers') {
            return ['id'];
        }

        return [];
    }

    public function updateField(Request $request, string $table, int $record): JsonResponse
    {
        $schema = MasterTableRegistry::schema($table);
        $modelClass = $schema['model'];
        $instance = $modelClass::query()->findOrFail($record);

        $field = (string) $request->input('field');

        if (! in_array($field, $schema['columns'], true)) {
            return response()->json(['message' => '不正な項目です。'], 422);
        }

        if (MasterFieldHelper::isReadonly($field)) {
            return response()->json(['message' => 'この項目は編集できません。'], 422);
        }

        if ($field === 'password' && ($request->input('value') === null || $request->input('value') === '')) {
            return response()->json([
                'success' => true,
                'field' => $field,
                'value' => null,
                'skipped' => true,
            ]);
        }

        $inputType = $schema['inputTypes'][$field];

        $validated = $request->validate([
            'value' => MasterFieldHelper::validationRulesForInputType($inputType),
        ]);

        $value = MasterFieldHelper::normalizeValue($inputType, $validated['value']);

        if ($field === 'password' && is_string($value)) {
            $value = Hash::make($value);
        }

        $instance->forceFill([$field => $value])->save();

        $fresh = $instance->fresh();

        return response()->json([
            'success' => true,
            'field' => $field,
            'value' => $fresh?->getAttribute($field),
            'display' => MasterFieldHelper::formatValueForDisplay($fresh ?? $instance, $field),
            'input' => MasterFieldHelper::formatValueForInput($fresh ?? $instance, $field, $inputType),
        ]);
    }
}
