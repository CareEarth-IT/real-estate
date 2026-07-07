<?php

namespace App\Http\Controllers;

use App\Models\PropertyDealDraft;
use App\Models\PropertyDealDraftAdFee;
use App\Support\PropertyDealDraftAdFees;
use App\Support\PropertyDealDraftCalculator;
use App\Support\PropertyDealDraftFiscalYear;
use App\Support\PropertyDealDraftForm;
use App\Support\PropertyDealDraftPropertyTaxes;
use App\Support\PropertyDealDraftSheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PropertyDealDraftController extends Controller
{
    public function index(Request $request): View
    {
        return view('property.deal-drafts.index', [
            'records' => PropertyDealDraft::query()
                ->with(['adFees', 'propertyTaxes'])
                ->orderBy('case_number')
                ->get()
                ->each(static function (PropertyDealDraft $draft): void {
                    $draft->fill(PropertyDealDraftCalculator::calculate($draft));
                }),
            'sheetRows' => PropertyDealDraftSheet::rows(),
            'visibleFiscalYears' => PropertyDealDraftFiscalYear::visibleYears(),
            'statusLabels' => config('property-deal-draft.statuses', []),
            'propertyTypeLabels' => config('property-deal-draft.property_types', []),
            'saved' => $request->has('saved'),
            'updated' => $request->has('updated'),
            'pageTitle' => '(仮)物件データ',
            'currentPage' => 'deal-drafts',
        ]);
    }

    public function create(): View
    {
        return view('property.deal-drafts.form', $this->formViewData(
            record: new PropertyDealDraft([
                'status' => 'for_sale',
                'property_type' => 'detached_house',
            ]),
            isEdit: false,
            submitLabel: '登録する',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->prepareAdFeesInput($request);
        $this->preparePropertyTaxesInput($request);
        $draft = PropertyDealDraft::query()->create($this->validatedAttributes($request));
        PropertyDealDraftAdFees::sync($draft, $request->input('ad_fees', []));
        PropertyDealDraftPropertyTaxes::sync($draft, $request->input('property_taxes', []));
        PropertyDealDraftCalculator::apply($draft);

        return redirect()->route('property.deal-drafts.index', ['saved' => 1]);
    }

    public function edit(PropertyDealDraft $propertyDealDraft): View
    {
        $propertyDealDraft->load(['adFees', 'propertyTaxes']);
        PropertyDealDraftCalculator::apply($propertyDealDraft);

        return view('property.deal-drafts.form', $this->formViewData(
            record: $propertyDealDraft,
            isEdit: true,
            submitLabel: '更新する',
        ));
    }

    public function update(Request $request, PropertyDealDraft $propertyDealDraft): RedirectResponse
    {
        $this->prepareAdFeesInput($request);
        $this->preparePropertyTaxesInput($request);
        $propertyDealDraft->update($this->validatedAttributes($request, $propertyDealDraft));
        PropertyDealDraftAdFees::sync($propertyDealDraft, $request->input('ad_fees', []));
        PropertyDealDraftPropertyTaxes::sync($propertyDealDraft, $request->input('property_taxes', []));
        PropertyDealDraftCalculator::apply($propertyDealDraft);

        return redirect()->route('property.deal-drafts.index', ['updated' => 1]);
    }

    public function updateField(Request $request, PropertyDealDraft $propertyDealDraft): JsonResponse
    {
        $allowedFields = ['status', 'property_type'];

        $validated = $request->validate([
            'field' => ['required', 'string', Rule::in($allowedFields)],
            'value' => ['nullable', 'string'],
        ]);

        $field = $validated['field'];
        $options = $field === 'status'
            ? array_keys(config('property-deal-draft.statuses', []))
            : array_keys(config('property-deal-draft.property_types', []));

        $value = $validated['value'] !== '' && $validated['value'] !== null
            ? $validated['value']
            : null;

        if ($value !== null && ! in_array($value, $options, true)) {
            return response()->json([
                'message' => '選択値が不正です。',
                'errors' => ['value' => ['選択値が不正です。']],
            ], 422);
        }

        if ($field === 'status' && $value === null) {
            return response()->json([
                'message' => '状況は必須です。',
                'errors' => ['value' => ['状況は必須です。']],
            ], 422);
        }

        $propertyDealDraft->update([$field => $value]);

        $labels = $field === 'status'
            ? config('property-deal-draft.statuses', [])
            : config('property-deal-draft.property_types', []);

        return response()->json([
            'success' => true,
            'field' => $field,
            'value' => $propertyDealDraft->{$field},
            'label' => $labels[$propertyDealDraft->{$field}] ?? null,
        ]);
    }

    public function storeAdFee(PropertyDealDraft $propertyDealDraft): JsonResponse
    {
        $sortOrder = (int) $propertyDealDraft->adFees()->max('sort_order') + 1;

        $adFee = $propertyDealDraft->adFees()->create([
            'agency_name' => '',
            'amount' => null,
            'sort_order' => $sortOrder,
        ]);

        $propertyDealDraft = PropertyDealDraftCalculator::apply($propertyDealDraft);

        return response()->json([
            'success' => true,
            'ad_fee' => [
                'id' => $adFee->id,
                'agency_name' => $adFee->agency_name,
                'amount' => $adFee->amount,
            ],
            'computed' => PropertyDealDraftCalculator::computedForResponse($propertyDealDraft),
        ]);
    }

    public function updateAdFee(Request $request, PropertyDealDraft $propertyDealDraft, PropertyDealDraftAdFee $adFee): JsonResponse
    {
        $this->ensureAdFeeBelongsToDraft($propertyDealDraft, $adFee);

        $payload = [];

        if ($request->has('agency_name')) {
            $payload['agency_name'] = trim((string) $request->input('agency_name'));
        }

        if ($request->has('amount')) {
            $payload['amount'] = PropertyDealDraftAdFees::parseAmount($request->input('amount'));
        }

        if ($payload === []) {
            return response()->json([
                'success' => true,
                'ad_fee' => [
                    'id' => $adFee->id,
                    'agency_name' => $adFee->agency_name,
                    'amount' => $adFee->amount,
                ],
                'computed' => PropertyDealDraftCalculator::computedForResponse($propertyDealDraft),
            ]);
        }

        if (array_key_exists('agency_name', $payload) && $payload['agency_name'] === '' && $adFee->agency_name === '') {
            unset($payload['agency_name']);
        }

        $agencyName = $payload['agency_name'] ?? $adFee->agency_name;

        if ($agencyName === '' && array_key_exists('amount', $payload)) {
            return response()->json([
                'message' => '金額を保存する前に仲介業者名を入力してください。',
                'errors' => ['agency_name' => ['金額を保存する前に仲介業者名を入力してください。']],
            ], 422);
        }

        if (array_key_exists('agency_name', $payload) && $payload['agency_name'] === '' && $adFee->agency_name !== '') {
            return response()->json([
                'message' => '仲介業者名を入力してください。',
                'errors' => ['agency_name' => ['仲介業者名を入力してください。']],
            ], 422);
        }

        $adFee->fill($payload);
        $adFee->save();

        $propertyDealDraft = PropertyDealDraftCalculator::apply($propertyDealDraft);

        return response()->json([
            'success' => true,
            'ad_fee' => [
                'id' => $adFee->id,
                'agency_name' => $adFee->agency_name,
                'amount' => $adFee->amount,
            ],
            'computed' => PropertyDealDraftCalculator::computedForResponse($propertyDealDraft),
        ]);
    }

    public function destroyAdFee(PropertyDealDraft $propertyDealDraft, PropertyDealDraftAdFee $adFee): JsonResponse
    {
        $this->ensureAdFeeBelongsToDraft($propertyDealDraft, $adFee);
        $adFee->delete();

        $propertyDealDraft = PropertyDealDraftCalculator::apply($propertyDealDraft);

        return response()->json([
            'success' => true,
            'computed' => PropertyDealDraftCalculator::computedForResponse($propertyDealDraft),
        ]);
    }

    /** @return array<string, mixed> */
    private function formViewData(PropertyDealDraft $record, bool $isEdit, string $submitLabel): array
    {
        if ($isEdit) {
            $record->loadMissing(['adFees', 'propertyTaxes']);
        }

        return [
            'record' => $record,
            'isEdit' => $isEdit,
            'submitLabel' => $submitLabel,
            'formRows' => PropertyDealDraftForm::rows(),
            'visibleFiscalYears' => PropertyDealDraftFiscalYear::visibleYears(),
            'statuses' => config('property-deal-draft.statuses', []),
            'propertyTypes' => config('property-deal-draft.property_types', []),
            'pageTitle' => $isEdit ? '(仮)物件データ編集' : '(仮)物件データ登録',
            'currentPage' => 'deal-drafts',
        ];
    }

    /** @return array<string, mixed> */
    private function validatedAttributes(Request $request, ?PropertyDealDraft $record = null): array
    {
        $request->merge(PropertyDealDraftForm::normalize($request->all()));

        $statusKeys = array_keys(config('property-deal-draft.statuses', []));
        $typeKeys = array_keys(config('property-deal-draft.property_types', []));

        $rules = [
            'case_number' => [
                'required',
                'string',
                'max:32',
                Rule::unique('property_deal_drafts', 'case_number')->ignore($record?->id),
            ],
            'status' => ['required', Rule::in($statusKeys)],
            'location' => ['nullable', 'string', 'max:255'],
            'property_type' => ['nullable', Rule::in($typeKeys)],
            'usage' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:64'],
            'ad_fees' => ['nullable', 'array'],
            'ad_fees.*.agency_name' => ['nullable', 'string', 'max:255'],
            'ad_fees.*.amount' => ['nullable', 'integer'],
            'property_taxes' => ['nullable', 'array'],
            'property_taxes.*.fiscal_year' => ['nullable', 'integer'],
            'property_taxes.*.amount' => ['nullable', 'integer'],
        ];

        foreach (PropertyDealDraftForm::integerKeys() as $key) {
            $rules[$key] = ['nullable', 'integer'];
        }

        foreach (PropertyDealDraftForm::percentKeys() as $key) {
            $rules[$key] = ['nullable', 'numeric'];
        }

        $validated = $request->validate($rules);

        unset($validated['ad_fees']);
        unset($validated['property_taxes']);

        foreach (PropertyDealDraftCalculator::computedKeys() as $key) {
            unset($validated[$key]);
        }

        return $validated;
    }

    private function prepareAdFeesInput(Request $request): void
    {
        $fees = $request->input('ad_fees', []);

        if (! is_array($fees)) {
            return;
        }

        foreach ($fees as $index => $fee) {
            if (! is_array($fee)) {
                continue;
            }

            $fees[$index]['amount'] = PropertyDealDraftAdFees::parseAmount($fee['amount'] ?? null);
        }

        $request->merge(['ad_fees' => $fees]);
    }

    private function preparePropertyTaxesInput(Request $request): void
    {
        $taxes = $request->input('property_taxes', []);

        if (! is_array($taxes)) {
            return;
        }

        foreach ($taxes as $index => $tax) {
            if (! is_array($tax)) {
                continue;
            }

            $taxes[$index]['amount'] = PropertyDealDraftPropertyTaxes::parseAmount($tax['amount'] ?? null);
        }

        $request->merge(['property_taxes' => $taxes]);
    }

    private function ensureAdFeeBelongsToDraft(PropertyDealDraft $propertyDealDraft, PropertyDealDraftAdFee $adFee): void
    {
        if ($adFee->property_deal_draft_id !== $propertyDealDraft->id) {
            abort(404);
        }
    }
}
