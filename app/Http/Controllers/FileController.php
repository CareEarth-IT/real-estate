<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function __construct(
        private readonly DocumentService $documentService,
    ) {}

    public function show(Request $request, Property $property, string $field): StreamedResponse
    {
        if (! $this->documentService->isAllowedField($field)) {
            abort(400, 'Invalid field');
        }

        $relativePath = $property->{$field};

        if (empty($relativePath)) {
            abort(404, 'File not found');
        }

        return $this->documentService->streamFile($relativePath);
    }
}
