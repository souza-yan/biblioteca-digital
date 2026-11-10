@props(['name', 'label'])

{{-- Stable keys and ignore.self preserve Alpine-owned attributes during Livewire morphs,
     while the real content remains fully rendered and reactive. --}}
<div {{ $attributes }} wire:key="skeleton-gate-{{ $name }}" wire:ignore.self data-skeleton-gate="{{ $name }}"
    x-data="{
        ready: false,
        pending: 0,
        timer: null,
        start() {
            if (this.ready || this.timer !== null) return;
            this.timer = setTimeout(() => { this.ready = true; this.timer = null; }, 450);
        },
        accepts(target) {
            return target === this.$el.closest('[wire\\:id]') || this.$el.contains(target);
        },
        begin(target) {
            if (!this.accepts(target)) return;
            this.pending++;
            clearTimeout(this.timer);
            this.timer = null;
            this.ready = false;
            this.start();
        },
        finish(target) { if (this.accepts(target)) this.pending = Math.max(0, this.pending - 1); },
        destroy() { clearTimeout(this.timer); },
    }" x-init="start()"
    x-on:app-request-start.window="begin($event.target)"
    x-on:app-request-end.window="finish($event.target)">
    <div wire:key="skeleton-visual-{{ $name }}" wire:ignore.self x-show="!ready || pending > 0"
        role="status" aria-busy="true" x-bind:aria-busy="ready && pending === 0 ? null : 'true'" aria-label="{{ $label }}">
        <div aria-hidden="true">{{ $skeleton }}</div>
        <span class="sr-only">Carregando…</span>
    </div>
    <div wire:key="skeleton-content-{{ $name }}" wire:ignore.self x-cloak x-show="ready && pending === 0">
        {{ $slot }}
    </div>
</div>
