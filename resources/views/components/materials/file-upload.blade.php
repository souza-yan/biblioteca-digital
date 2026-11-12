@props([
    'id',
    'model',
    'file' => null,
])

<div wire:key="material-upload-{{ $id }}" x-data="{ selectedFileName: @js($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile ? $file->getClientOriginalName() : null), uploading: false, progress: 0 }"
    x-init="$watch('$wire.' + @js($model), value => { if (! value) selectedFileName = null })"
    x-on:livewire-upload-start="uploading = true; progress = 0"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
    x-on:livewire-upload-finish="uploading = false"
    x-on:livewire-upload-cancel="uploading = false; progress = 0"
    x-on:livewire-upload-error="uploading = false; $tsui.interaction('toast').error('Falha no envio!', 'Não foi possível enviar o arquivo. Confira o formato, o tamanho e sua conexão e tente novamente.').send()">
    <label for="{{ $id }}" class="mb-1 block text-base font-medium text-gray-700">
        Arquivo <span aria-hidden="true" class="font-bold text-red-500">*</span>
    </label>
    <input id="{{ $id }}" type="file" wire:model="{{ $model }}" class="peer sr-only"
        accept="{{ implode(',', array_map(fn ($mime) => '.'.$mime, config('materials.upload.allowed_mimes'))) }}" aria-required="true"
        x-on:change="selectedFileName = $event.target.files[0]?.name ?? null">
    <div class="rounded-xl border border-gray-300 bg-white p-2 text-base text-gray-700 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-600">
        <label for="{{ $id }}"
            class="flex min-h-11 cursor-pointer items-center justify-center rounded-lg px-3 py-2 font-semibold transition focus-within:ring-2 focus-within:ring-blue-600"
            x-bind:class="selectedFileName ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'">
            <span x-text="selectedFileName ? 'Trocar arquivo' : 'Selecionar arquivo'"></span>
        </label>
        <p x-show="! selectedFileName" class="px-1 pt-2 text-sm text-gray-500">Nenhum arquivo selecionado</p>
    </div>
    <p class="mt-2 text-sm text-slate-600">
        Formatos: {{ strtoupper(implode(', ', config('materials.upload.allowed_mimes'))) }}.
        Máximo de {{ number_format(config('materials.upload.max_size_kilobytes') / 1024, 2, ',', '.') }} MB.
    </p>
    @error($model)
        <p class="mt-1 text-base text-red-600">{{ $message }}</p>
    @enderror
    <div x-cloak x-show="uploading" class="mt-2 text-sm font-medium text-blue-700" role="status">
        <span>Enviando arquivo: <span x-text="progress"></span>%</span>
        <progress max="100" x-bind:value="progress" aria-label="Progresso do envio do arquivo" class="mt-1 h-2 w-full"></progress>
    </div>
    @if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
        <dl class="mt-3 flex items-start gap-4 rounded-md bg-gray-50 p-3 text-base">
            <div class="min-w-0 flex-1">
                <dt class="font-medium text-gray-500">Nome</dt>
                <dd class="truncate text-gray-900" title="{{ $file->getClientOriginalName() }}">{{ $file->getClientOriginalName() }}</dd>
            </div>
            <div class="shrink-0 text-right">
                <dt class="font-medium text-gray-500">Tamanho</dt>
                <dd class="text-gray-900">{{ number_format($file->getSize() / 1024, 1, ',', '.') }} KB</dd>
            </div>
        </dl>
    @endif
</div>
