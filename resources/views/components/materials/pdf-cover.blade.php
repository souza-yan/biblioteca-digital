@props([
    'material',
    'version' => null,
    'alt' => null,
    'fit' => 'cover',
])

@php
    $mimeType = $version instanceof \App\Models\MaterialVersion ? $version->mime_type : null;
    $imageMimeTypes = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
    $hasCover = $version instanceof \App\Models\MaterialVersion
        && $mimeType === 'application/pdf'
        && filled($version->cover_path);
    $hasImageThumbnail = $version instanceof \App\Models\MaterialVersion
        && (int) $material->current_version_id === (int) $version->getKey()
        && in_array($mimeType, $imageMimeTypes, true)
        && $version->has_standard_preview;
    $coverUrl = $hasCover
        ? ($material->current_version_id === $version->getKey()
            ? route('covers.show', $material)
            : route('covers.version', [$material, $version]))
        : null;
    $imageUrl = $hasImageThumbnail ? route('thumbnails.show', $material) : null;
    $placeholder = match (true) {
        $mimeType === 'application/pdf' => ['icon' => 'document-text', 'label' => 'PDF', 'class' => 'bg-red-50 text-red-700'],
        str_contains((string) $mimeType, 'spreadsheetml') || str_contains((string) $mimeType, 'ms-excel') => ['icon' => 'table-cells', 'label' => 'Excel', 'class' => 'bg-emerald-50 text-emerald-700'],
        str_contains((string) $mimeType, 'presentationml') || str_contains((string) $mimeType, 'ms-powerpoint') => ['icon' => 'presentation-chart-bar', 'label' => 'PowerPoint', 'class' => 'bg-amber-50 text-amber-700'],
        str_contains((string) $mimeType, 'wordprocessingml') || $mimeType === 'application/msword' => ['icon' => 'document-text', 'label' => 'Word', 'class' => 'bg-blue-50 text-blue-700'],
        str_starts_with((string) $mimeType, 'image/') => ['icon' => 'photo', 'label' => 'Imagem', 'class' => 'bg-violet-50 text-violet-700'],
        str_starts_with((string) $mimeType, 'video/') => ['icon' => 'film', 'label' => 'Vídeo', 'class' => 'bg-slate-100 text-slate-700'],
        str_starts_with((string) $mimeType, 'audio/') => ['icon' => 'speaker-wave', 'label' => 'Áudio', 'class' => 'bg-slate-100 text-slate-700'],
        $mimeType === 'text/plain' => ['icon' => 'document-text', 'label' => 'Texto', 'class' => 'bg-slate-100 text-slate-700'],
        default => ['icon' => 'document', 'label' => 'Arquivo', 'class' => 'bg-slate-100 text-slate-700'],
    };
    $label = $alt ?? "Capa de {$material->title}";
@endphp

<div {{ $attributes->class('relative flex aspect-[2480/3508] items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100') }}>
    @if ($coverUrl)
        <img src="{{ $coverUrl }}" alt="{{ $label }}" @class([
            'size-full',
            'object-contain' => $fit === 'contain',
            'object-cover' => $fit !== 'contain',
        ]) loading="lazy">
    @elseif ($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $label }}" @class([
            'size-full',
            'object-contain' => $fit === 'contain',
            'object-cover' => $fit !== 'contain',
        ]) loading="lazy">
    @else
        <div role="img" aria-label="Capa indisponível para {{ $material->title }}"
            @class(['flex size-full flex-col items-center justify-center gap-2 px-3 text-center', $placeholder['class']])>
            <x-ts-icon :icon="$placeholder['icon']" outline class="size-10" />
            <span class="text-xs font-semibold uppercase tracking-wide">{{ $placeholder['label'] }}</span>
        </div>
    @endif
</div>
