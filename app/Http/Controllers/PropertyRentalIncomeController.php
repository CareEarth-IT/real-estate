<?php

namespace App\Http\Controllers;

use App\Models\PropertyRentalIncome;
use App\Support\PropertyRentalIncomeContractPeriod;
use App\Support\PropertyRentalIncomeListQuery;
use App\Support\PropertyRentalIncomeMonths;
use App\Support\YearMonth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PropertyRentalIncomeController extends Controller
{
    public function index(Request $request): View
    {
        $requestedMonth = (int) $request->query('month');
        $activePaymentMonth = $this->resolveActivePaymentMonth($requestedMonth);
        $paymentMonthTabs = PropertyRentalIncomeMonths::visibleTabs($activePaymentMonth);
        $list = PropertyRentalIncomeListQuery::resolve($request, $activePaymentMonth);

        return view('property.rental-income.index', [
            ...$list,
            'paymentMethodLabels' => config('property-rental-income.payment_methods', []),
            'paymentStatusLabels' => config('property-rental-income.payment_statuses', []),
            'paymentMonthTabs' => $paymentMonthTabs,
            'activePaymentMonth' => $activePaymentMonth,
            'listRoute' => 'property.rental-income.index',
            'listParams' => array_filter([
                'month' => $activePaymentMonth,
                'search' => $list['search'] !== '' ? $list['search'] : null,
                'payment_status' => $list['paymentStatus'],
            ]),
        ]);
    }

    public function all(Request $request): View
    {
        $list = PropertyRentalIncomeListQuery::resolve($request);

        return view('property.rental-income.all', [
            ...$list,
            'paymentMethodLabels' => config('property-rental-income.payment_methods', []),
            'paymentStatusLabels' => config('property-rental-income.payment_statuses', []),
            'listRoute' => 'property.rental-income.all',
            'listParams' => array_filter([
                'search' => $list['search'] !== '' ? $list['search'] : null,
                'payment_status' => $list['paymentStatus'],
                'sort' => self::activeSortParam($list['sort']),
                'direction' => self::activeSortParam($list['sort']) ? $list['sortDirection'] : null,
            ]),
            'sortableColumns' => config('property-rental-income.all_sortable_columns', []),
        ]);
    }

    private static function activeSortParam(string $sort): ?string
    {
        $allowed = array_keys(config('property-rental-income.all_sortable_columns', []));

        return in_array($sort, $allowed, true) ? $sort : null;
    }

    public function create(Request $request): View
    {
        $requestedMonth = (int) $request->query('month');
        $activePaymentMonth = $this->resolveActivePaymentMonth($requestedMonth);

        return view('property.rental-income.form', [
            'record' => new PropertyRentalIncome([
                'created_on' => now()->toDateString(),
                'payment_status' => 'unpaid',
            ]),
            'isEdit' => false,
            'submitLabel' => '登録する',
            'paymentMethods' => config('property-rental-income.payment_methods', []),
            'paymentStatuses' => config('property-rental-income.payment_statuses', []),
            'activePaymentMonth' => $activePaymentMonth,
            'returnMonth' => $activePaymentMonth,
            'contractPeriodEnabled' => PropertyRentalIncomeContractPeriod::enabled(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (
            PropertyRentalIncomeContractPeriod::enabled()
            && $this->hasContractPeriodInput($request)
        ) {
            return $this->storeWithContractPeriod($request);
        }

        $attributes = $this->validatedAttributes($request);
        PropertyRentalIncomeMonths::ensure($attributes['payment_month']);
        $record = PropertyRentalIncome::query()->create($attributes);

        return redirect()
            ->route('property.rental-income.index', ['month' => $record->payment_month])
            ->with('success', '家賃収入データを登録しました。');
    }

    public function edit(PropertyRentalIncome $propertyRentalIncome): View
    {
        return view('property.rental-income.form', [
            'record' => $propertyRentalIncome,
            'isEdit' => true,
            'submitLabel' => '更新する',
            'paymentMethods' => config('property-rental-income.payment_methods', []),
            'paymentStatuses' => config('property-rental-income.payment_statuses', []),
            'activePaymentMonth' => $propertyRentalIncome->payment_month,
            'returnMonth' => $propertyRentalIncome->payment_month,
        ]);
    }

    public function update(Request $request, PropertyRentalIncome $propertyRentalIncome): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request, $propertyRentalIncome);
        PropertyRentalIncomeMonths::ensure($attributes['payment_month']);
        $propertyRentalIncome->update($attributes);

        return redirect()
            ->route('property.rental-income.index', ['month' => $propertyRentalIncome->payment_month])
            ->with('success', '家賃収入データを更新しました。');
    }

    public function updateField(Request $request, PropertyRentalIncome $propertyRentalIncome): JsonResponse
    {
        if ($request->input('field') !== 'payment_status') {
            return response()->json(['message' => '不正な項目です。'], 422);
        }

        $paymentStatuses = array_keys(config('property-rental-income.payment_statuses', []));

        $validated = $request->validate([
            'field' => ['required', Rule::in(['payment_status'])],
            'value' => ['nullable', 'string', Rule::in($paymentStatuses)],
        ]);

        $value = $validated['value'] !== '' ? $validated['value'] : 'unpaid';

        $propertyRentalIncome->update([
            'payment_status' => $value,
        ]);

        return response()->json([
            'success' => true,
            'field' => 'payment_status',
            'value' => $propertyRentalIncome->payment_status,
        ]);
    }

    public function copyToNextMonth(PropertyRentalIncome $propertyRentalIncome): RedirectResponse
    {
        $nextPaymentOn = $this->resolveNextPaymentDate($propertyRentalIncome);

        if ($nextPaymentOn === null) {
            return redirect()
                ->route('property.rental-income.index', ['month' => $propertyRentalIncome->payment_month])
                ->with('error', '支払日が設定されていないため、次月へ複製できません。');
        }

        if (PropertyRentalIncome::query()->where('copied_from_id', $propertyRentalIncome->id)->exists()) {
            return redirect()
                ->route('property.rental-income.index', ['month' => $propertyRentalIncome->payment_month])
                ->with('error', 'このデータはすでに次月へ複製済みです。');
        }

        $nextPaymentMonth = (int) $nextPaymentOn->format('Ym');
        PropertyRentalIncomeMonths::ensure($nextPaymentMonth);

        $copy = $propertyRentalIncome->replicate();
        $copy->payment_on = $nextPaymentOn;
        $copy->payment_month = $nextPaymentMonth;
        $copy->copied_from_id = $propertyRentalIncome->id;
        $copy->save();

        return redirect()
            ->route('property.rental-income.index', ['month' => $copy->payment_month])
            ->with('success', '次月のデータを追加しました。');
    }

    public function destroy(Request $request, PropertyRentalIncome $propertyRentalIncome): RedirectResponse
    {
        $paymentMonth = $propertyRentalIncome->payment_month;
        $propertyRentalIncome->delete();

        $redirect = $request->input('redirect');

        if (is_string($redirect) && str_starts_with($redirect, url('/'))) {
            return redirect($redirect)->with('success', '家賃収入データを削除しました。');
        }

        $redirectMonth = PropertyRentalIncomeMonths::exists($paymentMonth)
            ? $paymentMonth
            : (PropertyRentalIncomeMonths::latestPaymentMonthWithData()
                ?? PropertyRentalIncomeMonths::visibleTabs($paymentMonth)[0]);

        return redirect()
            ->route('property.rental-income.index', array_filter([
                'month' => $redirectMonth,
            ]))
            ->with('success', '家賃収入データを削除しました。');
    }

    private function resolveActivePaymentMonth(int $requestedMonth): int
    {
        if ($requestedMonth > 0 && YearMonth::isValid($requestedMonth)) {
            return $requestedMonth;
        }

        return PropertyRentalIncomeMonths::latestPaymentMonthWithData()
            ?? (int) now()->format('Ym');
    }

    private function resolveNextPaymentDate(PropertyRentalIncome $record): ?Carbon
    {
        if ($record->payment_on !== null) {
            return $record->payment_on->copy()->addMonth();
        }

        if ($record->payment_month) {
            $year = intdiv((int) $record->payment_month, 100);
            $month = (int) $record->payment_month % 100;

            if ($month >= 1 && $month <= 12) {
                return Carbon::create($year, $month, 1)->addMonth();
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function validatedAttributes(Request $request, ?PropertyRentalIncome $record = null): array
    {
        $paymentMethods = array_keys(config('property-rental-income.payment_methods', []));
        $paymentStatuses = array_keys(config('property-rental-income.payment_statuses', []));
        $fallbackPaymentMonth = (int) $request->input(
            'fallback_payment_month',
            $record?->payment_month ?? $this->resolveActivePaymentMonth(0),
        );

        $validated = $request->validate([
            'created_on' => ['nullable', 'date'],
            'contractor' => ['nullable', 'string', 'max:5000'],
            'property_name' => ['nullable', 'string', 'max:5000'],
            'rent_year_month' => ['nullable', 'string'],
            'payment_method' => array_merge(['nullable', 'string'], $paymentMethods !== [] ? [Rule::in($paymentMethods)] : []),
            'rent_amount' => ['nullable', 'integer'],
            'payment_status' => array_merge(['nullable', 'string'], $paymentStatuses !== [] ? [Rule::in($paymentStatuses)] : []),
            'occupant_count' => ['nullable', 'integer', 'min:0', 'max:255'],
            'deposit_amount' => ['nullable', 'integer'],
            'payment_on' => ['nullable', 'date'],
            'fallback_payment_month' => ['nullable', 'integer'],
        ]);

        $paymentOn = $validated['payment_on'] ?? null;
        $paymentMonth = YearMonth::fromDate($paymentOn) ?? $fallbackPaymentMonth;

        return [
            'created_on' => $validated['created_on'] ?? null,
            'contractor' => $validated['contractor'] ?? null,
            'property_name' => $validated['property_name'] ?? null,
            'rent_year_month' => YearMonth::fromInput($validated['rent_year_month'] ?? null),
            'payment_method' => $validated['payment_method'] ?? null,
            'rent_amount' => $validated['rent_amount'] ?? null,
            'payment_status' => $validated['payment_status'] ?? 'unpaid',
            'occupant_count' => $validated['occupant_count'] ?? null,
            'deposit_amount' => $validated['deposit_amount'] ?? null,
            'payment_month' => $paymentMonth,
            'payment_on' => $paymentOn,
        ];
    }

    private function hasContractPeriodInput(Request $request): bool
    {
        return $request->filled('contract_start_on') || $request->filled('contract_end_on');
    }

    private function storeWithContractPeriod(Request $request): RedirectResponse
    {
        $request->validate([
            'contract_start_on' => ['required', 'date'],
            'contract_end_on' => ['required', 'date', 'after_or_equal:contract_start_on'],
        ], [], [
            'contract_start_on' => '契約開始日',
            'contract_end_on' => '契約満了日',
        ]);

        $contractStart = (string) $request->input('contract_start_on');
        $contractEnd = (string) $request->input('contract_end_on');
        $months = PropertyRentalIncomeContractPeriod::monthsBetween($contractStart, $contractEnd);

        if ($months === []) {
            return back()
                ->withInput()
                ->withErrors(['contract_end_on' => '契約満了日は契約開始日以降にしてください。']);
        }

        $maxMonths = PropertyRentalIncomeContractPeriod::maxMonths();

        if (count($months) > $maxMonths) {
            return back()
                ->withInput()
                ->withErrors([
                    'contract_end_on' => "契約期間は{$maxMonths}か月以内にしてください。",
                ]);
        }

        $baseAttributes = $this->validatedAttributes($request);
        $firstMonth = null;

        foreach ($months as $paymentMonth) {
            $attributes = $baseAttributes;
            $attributes['payment_month'] = $paymentMonth;
            $attributes['rent_year_month'] = $paymentMonth;
            $attributes['payment_on'] = PropertyRentalIncomeContractPeriod::paymentOnForMonth(
                $contractStart,
                $paymentMonth,
            );

            PropertyRentalIncomeMonths::ensure($paymentMonth);
            PropertyRentalIncome::query()->create($attributes);
            $firstMonth ??= $paymentMonth;
        }

        return redirect()
            ->route('property.rental-income.index', ['month' => $firstMonth])
            ->with('success', count($months).'件の家賃収入データを登録しました。（契約期限一括登録）');
    }
}
