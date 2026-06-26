<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlowManagement;
use App\Models\ScreeningCompletion;
use App\Models\SettlementManagement;
use App\Support\AdminListSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FlowManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = AdminListSearch::term($request->input('search'));

        ScreeningCompletion::query()
            ->whereHas('application', fn ($query) => $query->where('screening_ok', true))
            ->each(fn (ScreeningCompletion $screeningCompletion) => ScreeningCompletion::syncFromScreeningCompletion($screeningCompletion));

        $flowManagements = FlowManagement::query()
            ->with(['screeningCompletion.application'])
            ->whereHas('screeningCompletion', fn ($query) => $query
                ->where('flow_management_transition', true)
                ->whereHas('application', fn ($query) => $query->where('screening_ok', true)))
            ->join('screening_completions', 'flow_managements.screening_completion_id', '=', 'screening_completions.id')
            ->join('applications', 'screening_completions.application_id', '=', 'applications.id')
            ->tap(fn ($query) => AdminListSearch::applyToFlowManagement($query, $search))
            ->orderByDesc('applications.created_at')
            ->select('flow_managements.*')
            ->paginate(10)
            ->withQueryString();

        $booleanFields = FlowManagement::booleanFields();
        $columnLabels = FlowManagement::columnLabels();

        return view('admin.flow-managements.index', compact('flowManagements', 'booleanFields', 'columnLabels', 'search'));
    }

    public function updateField(Request $request, FlowManagement $flowManagement): JsonResponse
    {
        $field = $request->input('field');
        $allowedTextFields = ['memo', 'ad_fee_invoice_creation', 'document_deadline'];
        $allowedDateFields = ['scheduled_visit_date', 'key_handover_date'];

        if (in_array($field, FlowManagement::booleanFields(), true)) {
            $validated = $request->validate([
                'field' => ['required', Rule::in(FlowManagement::booleanFields())],
                'value' => ['required', 'boolean'],
            ]);
        } elseif (in_array($field, $allowedTextFields, true)) {
            $maxLength = match ($field) {
                'ad_fee_invoice_creation' => 50,
                'document_deadline' => 255,
                default => 2000,
            };
            $validated = $request->validate([
                'field' => ['required', Rule::in($allowedTextFields)],
                'value' => ['nullable', 'string', "max:{$maxLength}"],
            ]);
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

        $flowManagement->update([
            $validated['field'] => $validated['value'],
        ]);

        if ($validated['field'] === 'settlement_transition') {
            SettlementManagement::syncFromFlowManagement($flowManagement->fresh());
        }

        return response()->json([
            'success' => true,
            'field' => $validated['field'],
            'value' => $flowManagement->{$validated['field']},
        ]);
    }
}
