@props(['height' => 'h-48'])

<div {{ $attributes->class(["skeleton-shimmer flex w-full items-center justify-center rounded-xl animate-pulse", $height]) }}
    role="status" aria-label="Carregando imagem">
    <svg class="size-12 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="m3 16 5-7 6 6.5m6.5 2.5L16 13l-4.286 6M14 10h.01M4 19h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z" />
    </svg>
    <span class="sr-only">Carregando imagem...</span>
</div>
