<template x-teleport="body">
    <div id="version-preview-modal-{{ $material->id }}"
        x-cloak x-show="versionPreview !== null"
        x-trap.inert.noscroll="versionPreview !== null"
        x-on:keydown.escape.window="versionPreview = null"
        x-on:click.self="versionPreview = null"
        role="dialog" aria-modal="true" aria-labelledby="version-preview-title-{{ $material->id }}"
        class="fixed inset-0 z-[100] bg-slate-950/75 p-3 sm:p-6">
        <div class="flex h-full w-full flex-col overflow-hidden rounded-lg bg-white shadow-2xl">
            <div class="flex shrink-0 items-center justify-between gap-4 border-b border-slate-200 px-4 py-3 sm:px-6">
                <h2 id="version-preview-title-{{ $material->id }}" class="truncate text-base font-semibold text-slate-900"
                    x-text="versionPreview?.name ?? ''"></h2>
                <button type="button" x-on:click="versionPreview = null" aria-label="Fechar prévia da versão"
                    class="inline-flex size-10 shrink-0 items-center justify-center rounded-md text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <x-ts-icon icon="x-mark" outline class="size-6" />
                </button>
            </div>
            <div class="min-h-0 flex-1 bg-slate-100">
                <template x-if="versionPreview !== null">
                    <x-materials.preview-content />
                </template>
            </div>
        </div>
    </div>
</template>
