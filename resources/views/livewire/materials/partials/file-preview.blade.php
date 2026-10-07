<div class="mt-6 border-t border-gray-100 pt-5">
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
    </dl>

    <div class="mt-4 overflow-hidden rounded-md border border-gray-200 bg-gray-50">
        @if ($version->mime_type === 'application/pdf')
            <iframe
                class="h-[32rem] w-full"
                src="{{ $previewUrl }}"
                title="Prévia de {{ $version->original_name }}"
            ></iframe>
        @elseif (in_array($version->mime_type, ['image/png', 'image/jpeg', 'image/webp', 'image/gif'], true))
            <div class="flex min-h-64 items-center justify-center p-4">
                <img
                    class="max-h-[32rem] max-w-full object-contain"
                    src="{{ $previewUrl }}"
                    alt="Prévia de {{ $version->original_name }}"
                >
            </div>
        @elseif ($version->mime_type === 'text/plain')
            <iframe
                class="h-96 w-full bg-white"
                src="{{ $previewUrl }}"
                title="Prévia de {{ $version->original_name }}"
            ></iframe>
        @elseif (in_array($version->mime_type, ['video/mp4', 'video/webm'], true))
            <video class="max-h-[32rem] w-full" controls preload="metadata">
                <source src="{{ $previewUrl }}" type="{{ $version->mime_type }}">
                Seu navegador não oferece suporte à reprodução de vídeo.
            </video>
        @elseif ($version->mime_type === 'audio/mpeg')
            <div class="p-6">
                <audio class="w-full" controls preload="metadata">
                    <source src="{{ $previewUrl }}" type="{{ $version->mime_type }}">
                    Seu navegador não oferece suporte à reprodução de áudio.
                </audio>
            </div>
        @else
            <div class="flex items-center gap-3 p-6 text-sm text-gray-600">
                <svg class="h-8 w-8 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 3.75h6l5 5v11.5H7a2 2 0 0 1-2-2v-12.5a2 2 0 0 1 2-2Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 3.75v5h5M8 14h8m-8 3h8"/>
                </svg>
                <span>Prévia não disponível para este tipo</span>
            </div>
        @endif
    </div>
</div>
