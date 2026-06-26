<?php

namespace App\Http\Controllers;

use App\Services\DocumentService;
use App\Services\PropertyService;
use App\Support\DocumentFields;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferenceController extends Controller
{
    public function __construct(
        private readonly PropertyService $propertyService,
        private readonly DocumentService $documentService,
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'list');

        if (! in_array($tab, ['list', 'documents'], true)) {
            $tab = 'list';
        }

        return view('reference.index', [
            'properties' => $this->propertyService->getAll(),
            'tab' => $tab,
            'propertyService' => $this->propertyService,
            'documentLabels' => DocumentFields::labels(),
            'documentService' => $this->documentService,
            'pageTitle' => '参照一覧',
            'currentPage' => 'reference',
        ]);
    }
}
