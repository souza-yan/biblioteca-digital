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
        && in_array($mimeType, $imageMimeTypes, true);
    $coverUrl = $hasCover
        ? ($material->current_version_id === $version->getKey()
            ? route('covers.show', $material)
            : route('covers.version', [$material, $version]))
        : null;
    $imageUrl = $hasImageThumbnail ? route('thumbnails.show', $material) : null;
    $placeholder = match (true) {
        $mimeType === 'application/pdf' => ['icon' => 'document-text', 'label' => 'PDF'],
        str_starts_with((string) $mimeType, 'video/') => ['icon' => 'film', 'label' => 'Vídeo'],
        str_starts_with((string) $mimeType, 'audio/') => ['icon' => 'speaker-wave', 'label' => 'Áudio'],
        $mimeType === 'text/plain' => ['icon' => 'document-text', 'label' => 'Texto'],
        str_starts_with((string) $mimeType, 'application/') => ['icon' => 'archive-box', 'label' => 'Arquivo'],
        default => ['icon' => 'document', 'label' => 'Arquivo'],
    };
    $label = $alt ?? "Capa de {$material->title}";
@endphp

<div {{ $attributes->class('relative flex aspect-[3/4] items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100') }}>
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
            class="flex size-full flex-col items-center justify-center gap-2 bg-slate-100 px-3 text-center text-slate-500">
            <x-ts-icon :icon="$placeholder['icon']" outline class="size-10 text-slate-400" />
            <span class="text-xs font-semibold uppercase tracking-wide">{{ $placeholder['label'] }}</span>
        </div>
    @endif
</div>
