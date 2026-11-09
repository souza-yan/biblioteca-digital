@props(['state' => 'versionPreview'])

{{-- The state prop names a trusted Alpine object with url, name and type. --}}
<div {{ $attributes->class('h-full min-h-0 w-full bg-slate-100') }}>
    <template x-if="['image/png', 'image/jpeg', 'image/webp', 'image/gif'].includes({{ $state }}.type)">
        <div class="flex h-full w-full items-center justify-center p-4 sm:p-6">
            <img class="block h-full w-full object-contain" x-bind:src="{{ $state }}.url"
                x-bind:alt="'Prévia de ' + {{ $state }}.name">
        </div>
    </template>
    <template x-if="['application/pdf', 'text/plain'].includes({{ $state }}.type)">
        <iframe class="block h-full w-full border-0 bg-white"
            x-bind:src="{{ $state }}.url + ({{ $state }}.type === 'application/pdf' ? '#view=Fit' : '')"
            x-bind:title="'Prévia de ' + {{ $state }}.name"></iframe>
    </template>
    <template x-if="['video/mp4', 'video/webm'].includes({{ $state }}.type)">
        <div class="flex h-full w-full items-center justify-center p-4 sm:p-6">
            <video class="block h-full w-full object-contain" controls preload="metadata"
                x-bind:src="{{ $state }}.url" x-bind:aria-label="'Prévia de ' + {{ $state }}.name">
                Seu navegador não oferece suporte à reprodução de vídeo.
            </video>
        </div>
    </template>
    <template x-if="{{ $state }}.type === 'audio/mpeg'">
        <div class="flex h-full w-full items-center justify-center p-4 sm:p-6">
            <audio class="w-full max-w-xl" controls preload="metadata"
                x-bind:src="{{ $state }}.url" x-bind:aria-label="'Prévia de ' + {{ $state }}.name">
                Seu navegador não oferece suporte à reprodução de áudio.
            </audio>
        </div>
    </template>
    <template x-if="! ['application/pdf', 'text/plain', 'image/png', 'image/jpeg', 'image/webp', 'image/gif', 'video/mp4', 'video/webm', 'audio/mpeg'].includes({{ $state }}.type)">
        <div class="flex h-full flex-col items-center justify-center gap-3 p-6 text-center text-slate-600">
            <x-ts-icon icon="document" outline class="size-12 text-slate-400" />
            <p class="text-sm">A visualização não está disponível para este tipo de arquivo.</p>
        </div>
    </template>
</div>
