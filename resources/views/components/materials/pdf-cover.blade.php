@props([
    'material',
    'version' => null,
    'alt' => null,
    'fit' => 'cover',
])

@php
    $hasCover = $version instanceof \App\Models\MaterialVersion
        && $version->mime_type === 'application/pdf'
        && filled($version->cover_path);
    $coverUrl = $hasCover
        ? ($material->current_version_id === $version->getKey()
            ? route('covers.show', $material)
            : route('covers.version', [$material, $version]))
        : null;
    $label = $alt ?? "Capa de {$material->title}";
@endphp

<div {{ $attributes->class('relative flex aspect-[3/4] items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100') }}>
    @if ($coverUrl)
        <img src="{{ $coverUrl }}" alt="{{ $label }}" @class([
            'size-full',
            'object-contain' => $fit === 'contain',
            'object-cover' => $fit !== 'contain',
        ]) loading="lazy">
    @else
        <div role="img" aria-label="Capa indisponível para {{ $material->title }}"
            class="flex size-full flex-col items-center justify-center gap-2 bg-slate-100 px-3 text-center text-slate-500">
            <x-ts-icon icon="document-text" outline class="size-10 text-slate-400" />
            <span class="text-xs font-semibold uppercase tracking-wide">{{ $version?->mime_type === 'application/pdf' ? 'PDF' : 'Arquivo' }}</span>
        </div>
    @endif
</div>
