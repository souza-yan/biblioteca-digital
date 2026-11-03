<div x-data="{ isPreviewOpen: false }" class="mt-6 border-t border-gray-100 pt-5">
    <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
        <div class="sm:col-span-3">
            <dt class="font-semibold text-gray-500">Nome do arquivo</dt>
            <dd class="break-all text-gray-900">{{ $version->original_name }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-gray-500">Tipo</dt>
            <dd class="break-all text-gray-700">{{ $version->mime_type }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-gray-500">Tamanho</dt>
            <dd class="text-gray-700">{{ number_format($version->size / 1024, 1) }} KB</dd>
        </div>
        <div class="flex items-end sm:justify-end">
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-md bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                @click="isPreviewOpen = true"
            >
                <x-ts-icon icon="arrows-pointing-out" outline class="size-5" />
                Visualizar conteúdo
            </button>
        </div>
    </dl>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="isPreviewOpen"
            x-transition.opacity
            class="fixed inset-0 z-[100] bg-slate-950/75 p-3 sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="file-preview-title-{{ $version->getKey() }}"
            @keydown.escape.window="isPreviewOpen = false"
        >
            <div class="flex h-full w-full flex-col overflow-hidden rounded-lg bg-white shadow-2xl">
                <div class="flex shrink-0 items-center justify-between gap-4 border-b border-slate-200 px-4 py-3 sm:px-6">
                    <h2 id="file-preview-title-{{ $version->getKey() }}" class="truncate text-base font-semibold text-slate-900">
                        {{ $version->original_name }}
                    </h2>
                    <button
                        type="button"
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-md text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        @click="isPreviewOpen = false"
                        aria-label="Fechar visualização"
                    >
                        <x-ts-icon icon="x-mark" outline class="size-6" />
                    </button>
                </div>
                <div class="min-h-0 flex-1 bg-slate-100">
                    @if ($version->mime_type === 'application/pdf')
                    <iframe
                        class="min-h-0 flex-1 w-full bg-slate-100"
                        src="{{ $previewUrl }}#view=Fit"
                        title="Prévia de {{ $version->original_name }}"
                        loading="lazy"
                    ></iframe>
                    @elseif (in_array($version->mime_type, ['image/png', 'image/jpeg', 'image/webp', 'image/gif'], true))
                        <div class="flex h-full items-center justify-center p-4">
                            <img
                                class="max-h-full max-w-full object-contain"
                                src="{{ $previewUrl }}"
                                alt="Prévia de {{ $version->original_name }}"
                            >
                        </div>
                    @elseif ($version->mime_type === 'text/plain')
                        <iframe
                            class="h-full w-full bg-white"
                            src="{{ $previewUrl }}"
                            title="Prévia de {{ $version->original_name }}"
                            loading="lazy"
                        ></iframe>
                    @elseif (in_array($version->mime_type, ['video/mp4', 'video/webm'], true))
                        <div class="flex h-full items-center justify-center p-4">
                            <video class="max-h-full max-w-full" controls preload="metadata">
                                <source src="{{ $previewUrl }}" type="{{ $version->mime_type }}">
                                Seu navegador não oferece suporte à reprodução de vídeo.
                            </video>
                        </div>
                    @elseif ($version->mime_type === 'audio/mpeg')
                        <div class="flex h-full items-center p-6">
                            <audio class="w-full" controls preload="metadata">
                                <source src="{{ $previewUrl }}" type="{{ $version->mime_type }}">
                                Seu navegador não oferece suporte à reprodução de áudio.
                            </audio>
                        </div>
                    @else
                        <div class="flex h-full flex-col items-center justify-center gap-3 p-6 text-center text-slate-600">
                            <x-ts-icon icon="document" outline class="size-12 text-slate-400" />
                            <p class="text-sm">A visualização não está disponível para este tipo de arquivo.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>
