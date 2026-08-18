<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlowManagement;
use App\Models\SettlementManagement;
use App\Support\AdminListSearch;
use App\Support\FlowManagementListSort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FlowManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = AdminListSearch::term($request->input('search'));
        $sort = FlowManagementListSort::normalizeSort($request->input('sort'));
        $direction = FlowManagementListSort::normalizeDirection($request->input('direction'));

        $baseQuery = FlowManagement::query()
            ->with(['application'])
            ->where('flow_management_transition', true)
            ->whereHas('application', fn ($query) => $query->where('screening_ok', true))
            ->join('applications', 'flow_managements.application_id', '=', 'applications.id')
            ->tap(fn ($query) => AdminListSearch::applyToFlowManagement($query, $search))
            ->select('flow_managements.*');

        $deadlineWeekCounts = FlowManagementListSort::withinWeekCounts($baseQuery);

        $flowManagements = FlowManagementListSort::apply(clone $baseQuery, $sort, $direction)
            ->paginate(10)
            ->withQueryString();

        $booleanFields = FlowManagement::booleanFields();
        $columnLabels = FlowManagement::columnLabels();
        $sortOptions = FlowManagementListSort::options();
        [$weekFrom, $weekTo] = FlowManagementListSort::weekWindow();

        return view('admin.flow-managements.index', compact(
            'flowManagements',
            'booleanFields',
            'columnLabels',
            'search',
            'sort',
            'direction',
            'sortOptions',
            'deadlineWeekCounts',
            'weekFrom',
            'weekTo',
        ));
    }

    public function show(FlowManagement $flowManagement): View
    {
        $flowManagement->load(['application.customer']);

        return view('admin.flow-managements.show', [
            'flowManagement' => $flowManagement,
            'booleanFields' => FlowManagement::booleanFields(),
            'columnLabels' => FlowManagement::columnLabels(),
        ]);
    }

    public function updateField(Request $request, FlowManagement $flowManagement): JsonResponse
    {
        $field = $request->input('field');
        $allowedTextFields = [
            'memo',
            'ad_fee_invoice_creation',
            'google_drive_url',
            ...array_keys(FlowManagement::contractDocumentFields()),
        ];
        $allowedDateFields = ['move_in_date', 'document_deadline', 'scheduled_visit_date', 'key_handover_date'];
        $urlFields = [
            'google_drive_url',
            ...array_keys(FlowManagement::contractDocumentFields()),
        ];

        if (in_array($field, FlowManagement::booleanFields(), true)
            || in_array($field, FlowManagement::contractDocumentConfirmedFields(), true)) {
            $allowedBooleans = [
                ...FlowManagement::booleanFields(),
                ...FlowManagement::contractDocumentConfirmedFields(),
            ];
            $validated = $request->validate([
                'field' => ['required', Rule::in($allowedBooleans)],
                'value' => ['required', 'boolean'],
            ]);
        } elseif (in_array($field, $allowedTextFields, true)) {
            $maxLength = match ($field) {
                'ad_fee_invoice_creation' => 50,
                default => in_array($field, $urlFields, true) ? 2048 : 2000,
            };
            $valueRules = ['nullable', 'string', "max:{$maxLength}"];
            if (in_array($field, $urlFields, true)) {
                $valueRules[] = 'url';
                if ($request->input('value') === '') {
                    $request->merge(['value' => null]);
                }
            }
            $validated = $request->validate([
                'field' => ['required', Rule::in($allowedTextFields)],
                'value' => $valueRules,
            ]);
            if ($validated['value'] === '') {
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
        } elseif ($field === 'contract_doc_extra_links') {
            $rawLinks = $request->input('value');
            if (is_array($rawLinks)) {
                $normalized = [];
                foreach ($rawLinks as $link) {
                    if (! is_array($link)) {
                        continue;
                    }
                    $url = trim((string) ($link['url'] ?? ''));
                    $normalized[] = [
                        'name' => trim((string) ($link['name'] ?? '')),
                        'url' => $url === '' ? null : $url,
                    ];
                }
                $request->merge(['value' => $normalized]);
            }

            $validated = $request->validate([
                'field' => ['required', Rule::in(['contract_doc_extra_links'])],
                'value' => ['nullable', 'array'],
                'value.*.name' => ['nullable', 'string', 'max:100'],
                'value.*.url' => ['nullable', 'url', 'max:2048'],
            ]);

            $links = [];
            foreach ((array) ($validated['value'] ?? []) as $link) {
                if (! is_array($link)) {
                    continue;
                }
                $name = trim((string) ($link['name'] ?? ''));
                $url = $link['url'] ?? null;
                if ($name === '' && blank($url)) {
                    continue;
                }
                $links[] = [
                    'name' => $name,
                    'url' => filled($url) ? (string) $url : null,
                ];
            }
            $validated['value'] = $links;
        } else {
            return response()->json(['message' => '不正な項目です。'], 422);
        }

        $updates = [
            $validated['field'] => $validated['value'],
        ];

        if (in_array($validated['field'], array_keys(FlowManagement::contractDocumentFields()), true)
            && $flowManagement->{FlowManagement::contractDocumentConfirmedField($validated['field'])}) {
            return response()->json(['message' => '確認完了済みのため、リンクは変更できません。チェックを外してから変更してください。'], 422);
        }

        if (in_array($validated['field'], FlowManagement::contractDocumentConfirmedFields(), true)
            && $validated['value']
            && blank($flowManagement->{preg_replace('/_confirmed$/', '_url', $validated['field'])})) {
            return response()->json(['message' => 'リンクを入力してから確認完了にしてください。'], 422);
        }

        if (in_array($validated['field'], $urlFields, true)
            && blank($validated['value'])
            && in_array($validated['field'], array_keys(FlowManagement::contractDocumentFields()), true)) {
            $updates[FlowManagement::contractDocumentConfirmedField($validated['field'])] = false;
        }

        $flowManagement->update($updates);

        if (in_array($validated['field'], [
            'settlement_transition',
            'has_broker_fee',
            'staff_in_charge',
            'contractor',
            'property_name',
            'room_number',
            'entry_method',
        ], true)) {
            SettlementManagement::syncFromFlowManagement($flowManagement->fresh());
        }

        return response()->json([
            'success' => true,
            'field' => $validated['field'],
            'value' => $flowManagement->{$validated['field']},
        ]);
    }
}
