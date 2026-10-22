@props([
    'id',
    'model',
    'file' => null,
])

<div>
    <label for="{{ $id }}" class="mb-1 block text-base font-medium text-gray-700">
        Arquivo <span aria-hidden="true" class="font-bold text-red-500">*</span>
    </label>
    <input id="{{ $id }}" type="file" wire:model="{{ $model }}"
        accept="{{ implode(',', array_map(fn ($mime) => '.'.$mime, config('materials.upload.allowed_mimes'))) }}" required
        class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base text-gray-700 file:mr-4 file:rounded file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-base file:font-semibold file:text-blue-700">
    @error($model)
        <p class="mt-1 text-base text-red-600">{{ $message }}</p>
    @enderror
    <div wire:loading wire:target="{{ $model }}" class="mt-2 text-base font-medium text-blue-700">
        Enviando arquivo...
    </div>
    @if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
        <dl class="mt-3 grid grid-cols-1 gap-2 rounded-md bg-gray-50 p-3 text-base sm:grid-cols-2">
            <div class="min-w-0">
                <dt class="font-medium text-gray-500">Nome</dt>
                <dd class="truncate text-gray-900">{{ $file->getClientOriginalName() }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500">Tamanho</dt>
                <dd class="text-gray-900">{{ number_format($file->getSize() / 1024, 1, ',', '.') }} KB</dd>
            </div>
        </dl>
    @endif
</div>
