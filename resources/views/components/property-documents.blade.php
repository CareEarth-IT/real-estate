@php
    $propertyId = (int) $property->id;
@endphp

<div class="documents-grid">
    @foreach($documentLabels as $key => $label)
        @php
            $path = $property->{$key} ?? null;
            $fileUrl = ($path && $documentService->fileExists($path))
                ? route('files.show', ['property' => $propertyId, 'field' => $key])
                : null;
            $isPdf = $path && $documentService->isPdf($path);
        @endphp
    <div class="document-card">
        <h3>{{ $label }}</h3>
        <div class="document-preview">
            @if($fileUrl)
                @if($isPdf)
                <div class="pdf-preview">
                    <iframe src="{{ $fileUrl }}" title="{{ $label }}"></iframe>
                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline btn-sm">
                        新しいタブで開く
                    </a>
                </div>
                @else
                <a href="{{ $fileUrl }}" target="_blank">
                    <img src="{{ $fileUrl }}" alt="{{ $label }}" class="doc-full">
                </a>
                @endif
            @else
                <p class="doc-none">未登録</p>
            @endif
        </div>
    </div>
    @endforeach
</div>
