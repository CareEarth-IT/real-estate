<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CareEarthAuth;
use App\Models\Property;
use App\Services\DocumentService;
use App\Services\PropertyService;
use App\Support\DocumentFields;
use App\Support\Prefectures;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PropertyController extends Controller
{
    public function __construct(
        private readonly PropertyService $propertyService,
        private readonly DocumentService $documentService,
    ) {}

    public function index(): View
    {
        $listColumns = $this->propertyService->getListColumns();

        return view('properties.index', [
            'properties' => $this->propertyService->getAll(),
            'listColumns' => $listColumns,
            'toggleableColumns' => $this->toggleableColumns($listColumns),
            'documentShortLabels' => DocumentFields::shortLabels(),
            'documentService' => $this->documentService,
            'pageTitle' => 'マスターデータ一覧',
            'currentPage' => 'list',
        ]);
    }

    public function create(): View
    {
        return view('properties.form', array_merge(
            $this->propertyService->prepareFormContext($this->propertyService->defaultFormData()),
            $this->formViewData(isEdit: false, property: null, submitLabel: '登録する'),
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->persistForm(
            $request,
            $this->propertyService->defaultFormData(),
            fn (array $data, array $files): int => $this->propertyService->save($data, $files),
            fn (int $id): RedirectResponse => redirect()->route('properties.show', ['property' => $id, 'saved' => 1]),
        );
    }

    public function show(Request $request, Property $property): View
    {
        $fromReference = $request->query('from') === 'reference';

        return view('properties.show', [
            'property' => $property,
            'fromReference' => $fromReference,
            'showAccountingPrices' => ! $fromReference || CareEarthAuth::isKeiri($request),
            'saved' => $request->has('saved'),
            'updated' => $request->has('updated'),
            'isEdit' => false,
            'documentLabels' => DocumentFields::labels(),
            'documentService' => $this->documentService,
            'pageTitle' => '物件詳細 #'.$property->id,
            'currentPage' => $fromReference ? 'reference' : 'list',
        ]);
    }

    public function edit(Request $request, Property $property): View
    {
        return view('properties.show', array_merge(
            $this->propertyService->prepareFormContext(
                $this->propertyService->propertyToFormData($property),
            ),
            $this->formViewData(
                isEdit: true,
                property: $property,
                submitLabel: '更新する',
                fromReference: $request->query('from') === 'reference',
                request: $request,
            ),
        ));
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        return $this->persistForm(
            $request,
            $this->propertyService->propertyToFormData($property),
            fn (array $data, array $files): int => $this->propertyService->update((int) $property->id, $data, $files) ?: $property->id,
            function (int $id) use ($request): RedirectResponse {
                $params = ['property' => $id, 'updated' => 1];
                if ($request->query('from') === 'reference') {
                    $params['from'] = 'reference';
                }

                return redirect()->route('properties.show', $params);
            },
        );
    }

    /**
     * @param  list<array<string, mixed>>  $listColumns
     * @return list<array<string, mixed>>
     */
    private function toggleableColumns(array $listColumns): array
    {
        return array_values(array_filter(
            $listColumns,
            fn (array $col): bool => empty($col['alwaysVisible']),
        ));
    }

    /** @return array<string, mixed> */
    private function formViewData(
        bool $isEdit,
        ?Property $property,
        string $submitLabel,
        bool $fromReference = false,
        ?Request $request = null,
    ): array {
        return [
            'prefectures' => Prefectures::list(),
            'documentLabels' => DocumentFields::labels(),
            'documentService' => $this->documentService,
            'isEdit' => $isEdit,
            'property' => $property,
            'submitLabel' => $submitLabel,
            'fromReference' => $fromReference,
            'showAccountingPrices' => ! $fromReference || ($request && CareEarthAuth::isKeiri($request)),
            'saved' => false,
            'updated' => false,
            'pageTitle' => $isEdit ? '物件編集 #'.$property->id : 'データ登録',
            'currentPage' => $isEdit ? 'list' : 'form',
        ];
    }

    /**
     * @param  array<string, mixed>  $defaults
     * @param  Closure(array<string, mixed>, array<string, mixed>): int  $save
     * @param  Closure(int): RedirectResponse  $redirectTo
     */
    private function persistForm(
        Request $request,
        array $defaults,
        Closure $save,
        Closure $redirectTo,
    ): RedirectResponse {
        $formData = $this->propertyService->parseForm($request->all(), $defaults);

        if ($error = $this->propertyService->validate($formData)) {
            return back()->withInput()->withErrors(['form' => $error]);
        }

        try {
            $id = $save(
                $this->propertyService->propertyDataFromForm($formData),
                $this->propertyService->propertyFormFiles($request->allFiles()),
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['form' => $e->getMessage()]);
        }

        return $redirectTo($id);
    }
}
