@php
    $showPreviewButtonOnMobile = $showPreviewButtonOnMobile ?? false;
    $showPreviewAction = $showPreviewAction ?? true;
    $compact = $compact ?? false;
    $iconOnlyPreviewButton = $iconOnlyPreviewButton ?? false;
    $typeBadge = match (true) {
        $version->mime_type === 'application/pdf' => ['label' => 'PDF', 'class' => 'bg-red-50 text-red-700'],
        str_starts_with($version->mime_type, 'image/') => ['label' => 'Imagem', 'class' => 'bg-violet-50 text-violet-700'],
        str_contains($version->mime_type, 'spreadsheetml') || str_contains($version->mime_type, 'ms-excel') => ['label' => 'Excel', 'class' => 'bg-emerald-50 text-emerald-700'],
        str_contains($version->mime_type, 'presentationml') || str_contains($version->mime_type, 'ms-powerpoint') => ['label' => 'PowerPoint', 'class' => 'bg-amber-50 text-amber-700'],
        str_contains($version->mime_type, 'wordprocessingml') || $version->mime_type === 'application/msword' => ['label' => 'Word', 'class' => 'bg-blue-50 text-blue-700'],
        str_starts_with($version->mime_type, 'video/') => ['label' => 'Vídeo', 'class' => 'bg-slate-100 text-slate-700'],
        str_starts_with($version->mime_type, 'audio/') => ['label' => 'Áudio', 'class' => 'bg-slate-100 text-slate-700'],
        $version->mime_type === 'text/plain' => ['label' => 'Texto', 'class' => 'bg-slate-100 text-slate-700'],
        default => ['label' => 'Arquivo', 'class' => 'bg-slate-100 text-slate-700'],
    };
@endphp

<div @class([
    'border-t border-gray-100',
    'mt-3 pt-3' => $compact,
    'mt-4 pt-4 sm:mt-6 sm:pt-5' => ! $compact,
])>
    <dl @class([
        'grid text-sm',
        'grid-cols-2 gap-2' => $compact,
        'grid-cols-1 gap-2 sm:gap-3 sm:grid-cols-3' => ! $compact,
    ])>
        @if (! $compact)
            <div class="sm:col-span-3">
            <dt class="font-semibold text-gray-500">Nome do arquivo</dt>
            <dd class="break-all text-gray-900">{{ $version->original_name }}</dd>
            </div>
        @endif
        <div>
            <dt class="font-semibold text-gray-500">Tipo</dt>
            @if ($compact)
                <dd class="mt-1">
                    <span @class([
                        'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
                        $typeBadge['class'],
                    ])>{{ $typeBadge['label'] }}</span>
                </dd>
            @else
                <dd class="break-all text-gray-700">{{ $version->mime_type }}</dd>
            @endif
        </div>
        <div>
            <dt class="font-semibold text-gray-500">Tamanho</dt>
            <dd class="text-gray-700">{{ number_format($version->size / 1024, 1) }} KB</dd>
        </div>
        @if ($showPreviewAction)
            <div @class([
                'items-end sm:justify-end',
                'hidden sm:flex' => ! $showPreviewButtonOnMobile,
                'flex' => $showPreviewButtonOnMobile,
            ])>
                <button type="button" @class([
                    'inline-flex items-center rounded-md bg-blue-700 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                    'size-10 justify-center' => $iconOnlyPreviewButton,
                    'gap-2 px-4 py-2.5' => ! $iconOnlyPreviewButton,
                ]) @click="isPreviewOpen = true" aria-label="Visualizar conteúdo" title="Visualizar conteúdo">
                    <x-ts-icon icon="arrows-pointing-out" outline class="size-5" />
                    @if (! $iconOnlyPreviewButton)
                        Visualizar conteúdo
                    @endif
                </button>
            </div>
        @endif
    </dl>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="isPreviewOpen"
            x-transition.opacity
            class="fixed inset-0 z-[100] bg-slate-950/75 p-3 backdrop-blur-sm sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="file-preview-title-{{ $version->getKey() }}"
            @keydown.escape.window.capture="if (isPreviewOpen) { $event.stopImmediatePropagation(); isPreviewOpen = false; $dispatch('mobile-material-preview-closed') }"
        >
            <div class="flex h-full w-full flex-col overflow-hidden rounded-lg bg-white shadow-2xl">
                <div class="flex shrink-0 items-center justify-between gap-4 border-b border-slate-200 px-4 py-3 sm:px-6">
                    <h2 id="file-preview-title-{{ $version->getKey() }}" class="truncate text-base font-semibold text-slate-900">
                        {{ $version->original_name }}
                    </h2>
                    <button
                        type="button"
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-md text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        @click="isPreviewOpen = false; $dispatch('mobile-material-preview-closed')"
                        aria-label="Fechar visualização"
                    >
                        <x-ts-icon icon="x-mark" outline class="size-6" />
                    </button>
                </div>
                <div class="min-h-0 flex-1 bg-slate-100">
                    <template x-if="isPreviewOpen">
                        <div class="h-full w-full" data-preview-url="{{ $previewUrl }}"
                            data-preview-name="{{ $version->original_name }}" data-preview-type="{{ $version->mime_type }}"
                            x-data="{ filePreview: { url: $el.dataset.previewUrl, name: $el.dataset.previewName, type: $el.dataset.previewType } }">
                            <x-materials.preview-content state="filePreview" />
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>
