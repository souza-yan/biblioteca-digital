@props([
    'for',
    'required' => false,
])

<label for="{{ $for }}" {{ $attributes->class('block text-sm font-semibold text-slate-800') }}>
    {{ $slot }}
    @if ($required)
        <span class="font-bold text-red-600" aria-hidden="true">*</span>
        <span class="sr-only">(obrigatório)</span>
    @endif
</label>
