<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlowManagement;
use App\Models\SettlementManagement;
use App\Support\AdminListSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettlementManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = AdminListSearch::term($request->input('search'));

        FlowManagement::query()
            ->whereHas('screeningCompletion', fn ($query) => $query
                ->where('flow_management_transition', true)
                ->whereHas('application', fn ($query) => $query->where('screening_ok', true)))
            ->each(fn (FlowManagement $flowManagement) => SettlementManagement::syncFromFlowManagement($flowManagement));

        $settlementManagements = SettlementManagement::query()
            ->with(['flowManagement.screeningCompletion.application', 'customer'])
            ->whereHas('flowManagement', fn ($query) => $query
                ->where('settlement_transition', true)
                ->whereHas('screeningCompletion', fn ($query) => $query
                    ->where('flow_management_transition', true)
                    ->whereHas('application', fn ($query) => $query->where('screening_ok', true))))
            ->join('flow_managements', 'settlement_managements.flow_management_id', '=', 'flow_managements.id')
            ->join('screening_completions', 'flow_managements.screening_completion_id', '=', 'screening_completions.id')
            ->join('applications', 'screening_completions.application_id', '=', 'applications.id')
            ->tap(fn ($query) => AdminListSearch::applyToSettlementManagement($query, $search))
            ->orderByDesc('applications.created_at')
            ->select('settlement_managements.*')
            ->paginate(10)
            ->withQueryString();

        $booleanFields = SettlementManagement::booleanFields();
        $columnLabels = SettlementManagement::columnLabels();

        return view('admin.settlement-managements.index', compact('settlementManagements', 'booleanFields', 'columnLabels', 'search'));
    }

    public function updateField(Request $request, SettlementManagement $settlementManagement): JsonResponse
    {
        $field = $request->input('field');
        $allowedTextFields = ['earned_points', 'remarks'];
        $allowedIntegerFields = ['estimated_sales', 'sales_including_tax', 'sales_excluding_tax'];
        $allowedDateFields = ['settlement_transfer_date'];

        if (in_array($field, SettlementManagement::booleanFields(), true)) {
            $validated = $request->validate([
                'field' => ['required', Rule::in(SettlementManagement::booleanFields())],
                'value' => ['required', 'boolean'],
            ]);
        } elseif (in_array($field, $allowedTextFields, true)) {
            $maxLength = $field === 'remarks' ? 2000 : 255;
            $validated = $request->validate([
                'field' => ['required', Rule::in($allowedTextFields)],
                'value' => ['nullable', 'string', "max:{$maxLength}"],
            ]);
        } elseif (in_array($field, $allowedIntegerFields, true)) {
            $validated = $request->validate([
                'field' => ['required', Rule::in($allowedIntegerFields)],
                'value' => ['nullable', 'integer', 'min:0'],
            ]);
            if ($validated['value'] === '' || $validated['value'] === null) {
                $validated['value'] = null;
            }
        } elseif (in_array($field, $allowedDateFields, true)) {
            $validated = $request->validate([
                'field' => ['required', Rule::in($allowedDateFields)],
                'value' => ['nullable', 'date'],
            ]);
            if ($validated['value'] === '') {
                $validated['value'] = null;
            }
        } else {
            return response()->json(['message' => '不正な項目です。'], 422);
        }

        $settlementManagement->update([
            $validated['field'] => $validated['value'],
        ]);

        return response()->json([
            'success' => true,
            'field' => $validated['field'],
            'value' => $settlementManagement->{$validated['field']},
        ]);
    }
}
