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
        $maxLength = $field === 'google_drive_url' ? 2000 : 255;

        $validated = $request->validate([
            'field' => ['required', Rule::in(RentalPropertyArchive::editableFields())],
            'value' => ['nullable', 'string', "max:{$maxLength}"],
        ]);

        $value = $validated['value'];
        if ($value === '') {
            $value = null;
        }

        if ($validated['field'] === 'google_drive_url' && $value !== null) {
            $value = trim($value);
            if (! filter_var($value, FILTER_VALIDATE_URL)) {
                return response()->json(['message' => '有効なURLを入力してください。'], 422);
            }
        }

        $rentalPropertyArchive->update([
            $validated['field'] => $value,
        ]);

        return response()->json([
            'success' => true,
            'field' => $validated['field'],
            'value' => $rentalPropertyArchive->{$validated['field']},
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
