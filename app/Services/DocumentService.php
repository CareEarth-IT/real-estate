<?php

namespace App\Services;

use App\Support\DocumentFields;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentService
{
    /** @var array<string, list<string>> */
    private const ALLOWED_MIMES = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'pdf' => ['application/pdf'],
    ];

    /** @var array<string, string> */
    private const MIME_TYPES = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'pdf' => 'application/pdf',
    ];

    public function upload(?UploadedFile $file, string $prefix): ?string
    {
        if ($file === null || ! $file->isValid()) {
            if ($file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                throw new RuntimeException('ファイルのアップロードに失敗しました。');
            }

            return null;
        }

        $maxSize = (int) config('careearth.upload.max_size', 10 * 1024 * 1024);
        if ($file->getSize() > $maxSize) {
            throw new RuntimeException('ファイルサイズが上限（10MB）を超えています。');
        }

        $ext = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = config('careearth.upload.extensions', ['jpg', 'jpeg', 'png', 'pdf']);

        if (! in_array($ext, $allowedExtensions, true)) {
            throw new RuntimeException('許可されていないファイル形式です（jpg, png, pdf のみ）。');
        }

        $mime = $file->getMimeType() ?? '';
        if (! isset(self::ALLOWED_MIMES[$ext]) || ! in_array($mime, self::ALLOWED_MIMES[$ext], true)) {
            throw new RuntimeException('ファイルの内容が拡張子と一致しません。');
        }

        $filename = $prefix.'_'.bin2hex(random_bytes(8)).'.'.$ext;
        $file->move($this->uploadDir(), $filename);

        return 'uploads/documents/'.$filename;
    }

    /**
     * @param  array<string, UploadedFile|null>  $files
     * @param  array<string, string|null>|null  $existing
     * @return array<string, string|null>
     */
    public function resolvePaths(array $files, ?array $existing = null): array
    {
        $paths = [];

        foreach (DocumentFields::keys() as $field) {
            $paths[$field] = $existing[$field] ?? null;
        }

        foreach (DocumentFields::uploadPrefixes() as $field => $prefix) {
            $file = $files[$field] ?? null;

            if ($file instanceof UploadedFile && $file->getClientOriginalName() !== '') {
                if ($existing && ! empty($existing[$field])) {
                    $this->deleteFile($existing[$field]);
                }
                $paths[$field] = $this->upload($file, $prefix);
            }
        }

        return $paths;
    }

    public function resolveAbsolutePath(string $relativePath): ?string
    {
        $path = base_path($relativePath);

        return is_file($path) ? $path : null;
    }

    public function fileExists(?string $relativePath): bool
    {
        if ($relativePath === null || $relativePath === '') {
            return false;
        }

        return $this->resolveAbsolutePath($relativePath) !== null;
    }

    public function deleteFile(?string $relativePath): void
    {
        $absolute = $relativePath ? $this->resolveAbsolutePath($relativePath) : null;

        if ($absolute !== null && is_file($absolute)) {
            unlink($absolute);
        }
    }

    public function streamFile(string $relativePath): StreamedResponse
    {
        $absolute = $this->resolveAbsolutePath($relativePath);

        if ($absolute === null) {
            abort(404, 'File not found');
        }

        $uploadRoot = realpath($this->uploadDir());
        $realFile = realpath($absolute);

        if ($uploadRoot === false || $realFile === false || ! str_starts_with($realFile, $uploadRoot)) {
            abort(403, 'Access denied');
        }

        $ext = $this->getFileExtension($relativePath);
        $mime = self::MIME_TYPES[$ext] ?? 'application/octet-stream';

        return response()->stream(function () use ($absolute): void {
            $handle = fopen($absolute, 'rb');
            if ($handle === false) {
                return;
            }
            fpassthru($handle);
            fclose($handle);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) filesize($absolute),
            'Content-Disposition' => 'inline; filename="'.basename($relativePath).'"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function getFileExtension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    public function isPdf(string $path): bool
    {
        return $this->getFileExtension($path) === 'pdf';
    }

    public function isAllowedField(string $field): bool
    {
        return DocumentFields::isValid($field);
    }

    private function uploadDir(): string
    {
        $dir = base_path('uploads/documents');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }
}
