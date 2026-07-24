<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RentalPropertyArchive;
use App\Models\RentalPropertyArchiveImage;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RentalPropertyArchiveController extends Controller
{
    public function __construct(
        private readonly DocumentService $documentService,
    ) {}

    public function index(): View
    {
        $archives = RentalPropertyArchive::query()
            ->with('images')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.rental-property-archives.index', [
            'archives' => $archives,
            'columnLabels' => RentalPropertyArchive::columnLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $archive = RentalPropertyArchive::query()->create([
            'property_name' => null,
            'address' => null,
            'building_age' => null,
            'floor_plan_type' => 'R',
        ]);

        return redirect()
            ->route('admin.rental-property-archives.show', $archive)
            ->with('success', '賃貸物件を追加しました。');
    }

    public function show(RentalPropertyArchive $rentalPropertyArchive): View
    {
        $rentalPropertyArchive->load('images');

        return view('admin.rental-property-archives.show', [
            'archive' => $rentalPropertyArchive,
            'columnLabels' => RentalPropertyArchive::columnLabels(),
        ]);
    }

    public function updateField(Request $request, RentalPropertyArchive $rentalPropertyArchive): JsonResponse
    {
        $field = $request->input('field');
        if (! is_string($field) || ! in_array($field, RentalPropertyArchive::editableFields(), true)) {
            return response()->json(['message' => '不正な項目です。'], 422);
        }

        if (in_array($field, RentalPropertyArchive::booleanFields(), true)) {
            $validated = $request->validate([
                'value' => ['nullable', 'boolean'],
            ]);
            $value = (bool) ($validated['value'] ?? false);
        } elseif (in_array($field, RentalPropertyArchive::integerFields(), true)) {
            $raw = $request->input('value');
            if ($raw === '' || $raw === null) {
                $rentalPropertyArchive->update([$field => null]);

                return response()->json([
                    'success' => true,
                    'field' => $field,
                    'value' => null,
                ]);
            }

            $rules = ['required', 'integer', 'min:0'];
            if ($field === 'built_month') {
                $rules = ['required', 'integer', 'min:1', 'max:12'];
            }
            if ($field === 'built_year') {
                $rules = ['required', 'integer', 'min:1800', 'max:2100'];
            }
            if (in_array($field, ['monthly_rent_minor', 'collateral_amount_minor', 'area_minor', 'balcony_area_minor', 'utility_cost_minor'], true)) {
                $rules = ['required', 'integer', 'min:0', 'max:9'];
            }
            if ($field === 'contract_months') {
                $rules = ['required', 'integer', 'min:0', 'max:11'];
            }
            if ($field === 'insulation_grade') {
                $rules = ['required', 'integer', 'min:1', 'max:7'];
            }

            $validated = $request->validate([
                'value' => $rules,
            ]);
            $value = (int) $validated['value'];
        } elseif (array_key_exists($field, RentalPropertyArchive::arrayFields())) {
            $slotCount = RentalPropertyArchive::arrayFields()[$field];
            $validated = $request->validate([
                'value' => ['nullable', 'array', 'max:'.$slotCount],
                'value.*' => ['nullable', 'integer', 'min:0'],
            ]);
            $raw = array_values($validated['value'] ?? []);
            $normalized = [];
            for ($i = 0; $i < $slotCount; $i++) {
                $slot = $raw[$i] ?? null;
                $normalized[] = ($slot === '' || $slot === null) ? null : (int) $slot;
            }
            // Trim trailing nulls for cleaner storage, keep leading structure.
            while ($normalized !== [] && end($normalized) === null) {
                array_pop($normalized);
            }
            $value = $normalized === [] ? null : $normalized;
        } elseif ($field === 'surroundings') {
            $rowCount = RentalPropertyArchive::surroundingsRowCount();
            $categories = RentalPropertyArchive::surroundingCategories();
            $validated = $request->validate([
                'value' => ['nullable', 'array', 'max:'.$rowCount],
                'value.*.category' => ['nullable', 'string', Rule::in($categories)],
                'value.*.place_name' => ['nullable', 'string', 'max:255'],
                'value.*.meters' => ['nullable', 'integer', 'min:0'],
                'value.*.google_drive_url' => ['nullable', 'string', 'max:2000'],
            ]);

            $raw = array_values($validated['value'] ?? []);
            $normalized = [];
            for ($i = 0; $i < $rowCount; $i++) {
                $row = $raw[$i] ?? [];
                $category = trim((string) ($row['category'] ?? ''));
                $placeName = trim((string) ($row['place_name'] ?? ''));
                $meters = $row['meters'] ?? null;
                $driveUrl = trim((string) ($row['google_drive_url'] ?? ''));

                if ($meters === '' || $meters === null) {
                    $meters = null;
                } else {
                    $meters = (int) $meters;
                }

                if ($driveUrl !== '') {
                    if (! filter_var($driveUrl, FILTER_VALIDATE_URL)) {
                        return response()->json(['message' => '有効なGoogleドライブURLを入力してください。'], 422);
                    }
                } else {
                    $driveUrl = null;
                }

                if ($category === '' && $placeName === '' && $meters === null && $driveUrl === null) {
                    continue;
                }

                $normalized[] = [
                    'category' => $category !== '' ? $category : null,
                    'place_name' => $placeName !== '' ? $placeName : null,
                    'meters' => $meters,
                    'google_drive_url' => $driveUrl,
                ];
            }

            $value = $normalized === [] ? null : $normalized;
        } elseif (array_key_exists($field, RentalPropertyArchive::tagGroupOptions())) {
            $allowed = RentalPropertyArchive::tagGroupOptions()[$field];
            $validated = $request->validate([
                'value' => ['nullable', 'array'],
                'value.*' => ['string', Rule::in($allowed)],
            ]);
            $value = array_values(array_unique($validated['value'] ?? []));
            $value = $value === [] ? null : $value;
        } elseif (in_array($field, RentalPropertyArchive::dateFields(), true)) {
            $raw = $request->input('value');
            if ($raw === '' || $raw === null) {
                $rentalPropertyArchive->update([$field => null]);

                return response()->json([
                    'success' => true,
                    'field' => $field,
                    'value' => null,
                ]);
            }

            $validated = $request->validate([
                'value' => ['required', 'date'],
            ]);
            $value = $validated['value'];
        } elseif (array_key_exists($field, RentalPropertyArchive::enumFieldOptions())) {
            $validated = $request->validate([
                'value' => ['nullable', 'string', Rule::in(RentalPropertyArchive::enumFieldOptions()[$field])],
            ]);
            $value = $validated['value'] ?: null;
        } else {
            $maxLength = $field === 'google_drive_url' ? 2000 : 255;
            $validated = $request->validate([
                'value' => ['nullable', 'string', "max:{$maxLength}"],
            ]);
            $value = $validated['value'];
            if ($value === '') {
                $value = null;
            }

            if ($field === 'google_drive_url' && $value !== null) {
                $value = trim($value);
                if (! filter_var($value, FILTER_VALIDATE_URL)) {
                    return response()->json(['message' => '有効なURLを入力してください。'], 422);
                }
            }

            if ($field === 'postal_code' && $value !== null) {
                $value = preg_replace('/[^\d\-]/', '', $value) ?: null;
            }
        }

        $rentalPropertyArchive->update([
            $field => $value,
        ]);

        // Keep legacy address in sync for list cards.
        if ($field === 'location') {
            $rentalPropertyArchive->update(['address' => $value]);
        }

        return response()->json([
            'success' => true,
            'field' => $field,
            'value' => $rentalPropertyArchive->{$field},
        ]);
    }

    public function storeImages(Request $request, RentalPropertyArchive $rentalPropertyArchive): JsonResponse
    {
        $request->validate([
            'images' => ['required'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png', 'max:10240'],
        ], [
            'images.required' => '画像を選択してください。',
            'images.*.mimes' => '画像は jpg / png のみ登録できます。',
            'images.*.max' => '各画像は10MB以下にしてください。',
        ]);

        $files = $request->file('images', []);
        if (! is_array($files)) {
            $files = [$files];
        }

        $files = array_values(array_filter($files, fn ($file) => $file instanceof UploadedFile));
        if ($files === []) {
            return response()->json(['message' => '画像を選択してください。'], 422);
        }

        $nextSort = (int) $rentalPropertyArchive->images()->max('sort_order') + 1;
        $created = [];

        try {
            foreach ($files as $index => $file) {
                $path = $this->documentService->uploadImage($file, 'rental_archive_'.$rentalPropertyArchive->id);
                if ($path === null) {
                    continue;
                }

                $image = $rentalPropertyArchive->images()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'sort_order' => $nextSort + $index,
                ]);

                $created[] = $this->imagePayload($rentalPropertyArchive, $image);
            }
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($created === []) {
            return response()->json(['message' => '画像のアップロードに失敗しました。'], 422);
        }

        $rentalPropertyArchive->touch();

        return response()->json([
            'success' => true,
            'images' => $created,
        ]);
    }

    public function destroyImage(
        RentalPropertyArchive $rentalPropertyArchive,
        RentalPropertyArchiveImage $image,
    ): JsonResponse {
        if ((int) $image->rental_property_archive_id !== (int) $rentalPropertyArchive->id) {
            abort(404);
        }

        $this->documentService->deleteFile($image->path);
        $image->delete();
        $rentalPropertyArchive->touch();

        return response()->json(['success' => true]);
    }

    public function showImage(
        RentalPropertyArchive $rentalPropertyArchive,
        RentalPropertyArchiveImage $image,
    ): StreamedResponse {
        if ((int) $image->rental_property_archive_id !== (int) $rentalPropertyArchive->id) {
            abort(404);
        }

        return $this->documentService->streamFile($image->path);
    }

    public function destroy(RentalPropertyArchive $rentalPropertyArchive): RedirectResponse
    {
        $rentalPropertyArchive->load('images');

        foreach ($rentalPropertyArchive->images as $image) {
            $this->documentService->deleteFile($image->path);
        }

        $rentalPropertyArchive->delete();

        return redirect()
            ->route('admin.rental-property-archives.index')
            ->with('success', '賃貸物件を削除しました。');
    }

    /**
     * @return array{id: int, url: string, original_name: string|null}
     */
    private function imagePayload(RentalPropertyArchive $archive, RentalPropertyArchiveImage $image): array
    {
        return [
            'id' => $image->id,
            'url' => route('admin.rental-property-archives.images.show', [$archive, $image]),
            'original_name' => $image->original_name,
        ];
    }
}
