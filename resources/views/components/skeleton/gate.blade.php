@props(['name', 'label'])

{{-- Stable keys and ignore.self preserve Alpine-owned attributes during Livewire morphs,
     while the real content remains fully rendered and reactive. --}}
<div {{ $attributes }} wire:key="skeleton-gate-{{ $name }}" wire:ignore.self data-skeleton-gate="{{ $name }}"
    x-data="{
        ready: false,
        timer: null,
        start() {
            if (this.ready || this.timer !== null) return;
            this.timer = setTimeout(() => { this.ready = true; this.timer = null; }, 650);
        },
        destroy() { clearTimeout(this.timer); },
    }" x-init="start()">
    <div wire:key="skeleton-visual-{{ $name }}" wire:ignore.self x-show="!ready"
        role="status" aria-busy="true" x-bind:aria-busy="ready ? null : 'true'" aria-label="{{ $label }}">
        <div aria-hidden="true">{{ $skeleton }}</div>
        <span class="sr-only">Carregando…</span>
    </div>
    <div wire:key="skeleton-content-{{ $name }}" wire:ignore.self x-cloak x-show="ready">
        {{ $slot }}
    </div>
</div>
