<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlowManagement;
use App\Models\SettlementManagement;
use App\Services\SettlementInvoiceCsvService;
use App\Services\SettlementReceiptService;
use App\Support\AdminListSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettlementManagementController extends Controller
{
    public function __construct(
        private readonly SettlementInvoiceCsvService $invoiceCsvService,
        private readonly SettlementReceiptService $receiptService,
    ) {}

    public function index(Request $request): View
    {
        $search = AdminListSearch::term($request->input('search'));

        FlowManagement::query()
            ->where('flow_management_transition', true)
            ->whereHas('application', fn ($query) => $query->where('screening_ok', true))
            ->each(fn (FlowManagement $flowManagement) => SettlementManagement::syncFromFlowManagement($flowManagement));

        $settlementManagements = SettlementManagement::query()
            ->with(['flowManagement.application', 'customer'])
            ->whereHas('flowManagement', fn ($query) => $query
                ->where('settlement_transition', true)
                ->where('flow_management_transition', true)
                ->whereHas('application', fn ($query) => $query->where('screening_ok', true)))
            ->join('flow_managements', 'settlement_managements.flow_management_id', '=', 'flow_managements.id')
            ->join('applications', 'flow_managements.application_id', '=', 'applications.id')
            ->tap(fn ($query) => AdminListSearch::applyToSettlementManagement($query, $search))
            ->orderByDesc('applications.created_at')
            ->select('settlement_managements.*')
            ->paginate(10)
            ->withQueryString();

        $booleanFields = SettlementManagement::booleanFields();
        $columnLabels = SettlementManagement::columnLabels();
        $upcomingTransferCount = $settlementManagements->getCollection()
            ->filter(fn (SettlementManagement $settlementManagement): bool => $settlementManagement->shouldShowSettlementTransferCountdown())
            ->count();

        return view('admin.settlement-managements.index', compact(
            'settlementManagements',
            'booleanFields',
            'columnLabels',
            'search',
            'upcomingTransferCount',
        ));
    }

    public function show(SettlementManagement $settlementManagement): View
    {
        $settlementManagement->load(['flowManagement.application.customer', 'customer']);

        return view('admin.settlement-managements.show', [
            'settlementManagement' => $settlementManagement,
            'booleanFields' => SettlementManagement::booleanFields(),
            'columnLabels' => SettlementManagement::columnLabels(),
        ]);
    }

    public function downloadInvoice(SettlementManagement $settlementManagement): StreamedResponse
    {
        $settlementManagement->load(['flowManagement.application.customer', 'customer']);

        $binary = $this->invoiceCsvService->build($settlementManagement);
        $filename = $this->invoiceCsvService->downloadFilename($settlementManagement);

        return response()->streamDownload(function () use ($binary): void {
            echo $binary;
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadReceipt(SettlementManagement $settlementManagement): StreamedResponse
    {
        $settlementManagement->load(['flowManagement.application.customer', 'customer']);

        $binary = $this->receiptService->build($settlementManagement);
        $filename = $this->receiptService->downloadFilename($settlementManagement);

        return response()->streamDownload(function () use ($binary): void {
            echo $binary;
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function updateField(Request $request, SettlementManagement $settlementManagement): JsonResponse
    {
        $field = $request->input('field');
        $allowedTextFields = ['management_number', 'earned_points', 'remarks'];
        $allowedIntegerFields = ['estimated_sales', 'advertising_fee_amount', 'broker_fee_amount', 'sales_including_tax', 'sales_excluding_tax'];
        $allowedDateFields = ['contract_date', 'settlement_transfer_date'];

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

        if (in_array($validated['field'], ['advertising_fee_amount', 'broker_fee_amount'], true)) {
            $settlementManagement->forceFill([
                'estimated_sales' => (int) ($settlementManagement->advertising_fee_amount ?? 0)
                    + (int) ($settlementManagement->broker_fee_amount ?? 0),
            ])->save();
        }

        return response()->json([
            'success' => true,
            'field' => $validated['field'],
            'value' => $settlementManagement->{$validated['field']},
        ]);
    }
}
