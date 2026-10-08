@props([
    'fields',
    'optionGroups',
    'clearMethod',
    'gridColumns' => 'md:grid-cols-2 xl:grid-cols-4',
])

<div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
    <div {{ $attributes->class('grid grid-cols-1 gap-4 '.$gridColumns) }}>
        @foreach ($fields as $field)
            <label class="block text-sm font-medium text-gray-700">
                {{ $field['label'] }}

                @if ($field['type'] === 'select')
                    <select
                        wire:model.live="{{ $field['model'] }}"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm"
                    >
                        <option value="">{{ $field['placeholder'] }}</option>
                        @foreach ($optionGroups[$field['options']] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @else
                    <input
                        wire:model.live="{{ $field['model'] }}"
                        type="date"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm"
                    >
                @endif
            </label>
        @endforeach
    </div>

    <div class="mt-4 flex justify-end">
        <x-ts-button color="blue" class="text-white" wire:click="{{ $clearMethod }}">
            Limpar filtros
        </x-ts-button>
    </div>

    @foreach ($fields as $field)
        @error($field['model'])
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    @endforeach
</div>
