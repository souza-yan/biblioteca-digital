@props([
    'id',
    'label',
    'required' => false,
    'error' => null,
])

<div>
    <x-form.field-label :for="$id" :required="$required">{{ $label }}</x-form.field-label>
    <input id="{{ $id }}" {{ $required ? 'required' : '' }}
        {{ $attributes->class('mt-1 block h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-base text-slate-900 shadow-sm focus:border-blue-600 focus:ring-blue-600') }}>
    @if ($error)
        @error($error)
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>
