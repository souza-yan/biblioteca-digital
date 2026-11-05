@props([
    'id',
    'model',
    'file' => null,
])

<div x-data="{ selectedFileName: @js($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile ? $file->getClientOriginalName() : null) }">
    <label for="{{ $id }}" class="mb-1 block text-base font-medium text-gray-700">
        Arquivo <span aria-hidden="true" class="font-bold text-red-500">*</span>
    </label>
    <input id="{{ $id }}" type="file" wire:model="{{ $model }}" class="sr-only"
        accept="{{ implode(',', array_map(fn ($mime) => '.'.$mime, config('materials.upload.allowed_mimes'))) }}" required
        x-on:change="selectedFileName = $event.target.files[0]?.name ?? null">
    <div class="rounded-xl border border-gray-300 bg-white p-2 text-base text-gray-700">
        <label for="{{ $id }}"
            class="flex min-h-11 cursor-pointer items-center justify-center rounded-lg px-3 py-2 font-semibold transition focus-within:ring-2 focus-within:ring-blue-600"
            x-bind:class="selectedFileName ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'">
            <span x-text="selectedFileName ? 'Trocar arquivo' : 'Selecionar arquivo'"></span>
        </label>
        <p x-show="! selectedFileName" class="px-1 pt-2 text-sm text-gray-500">Nenhum arquivo selecionado</p>
    </div>
    @error($model)
        <p class="mt-1 text-base text-red-600">{{ $message }}</p>
    @enderror
    <div wire:loading wire:target="{{ $model }}" class="mt-2 text-base font-medium text-blue-700">
        Enviando arquivo...
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
