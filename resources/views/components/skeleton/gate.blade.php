@props(['name', 'label', 'paginationPage' => null, 'initial' => true])

{{-- Stable keys and ignore.self preserve Alpine-owned attributes during Livewire morphs,
     while the real content remains fully rendered and reactive. --}}
<div {{ $attributes }} wire:key="skeleton-gate-{{ $name }}" wire:ignore.self data-skeleton-gate="{{ $name }}"
    x-data="{
        ready: @js(! $initial),
        pending: false,
        timer: null,
        unsubscribe: null,
        watchPagination() {
            this.unsubscribe = this.$wire.$hook('commit', ({ commit, succeed, fail }) => {
                const paginating = commit.calls.some(call => {
                    if (! ['gotoPage', 'nextPage', 'previousPage'].includes(call.method)) return false;
                    const pageName = call.params[call.method === 'gotoPage' ? 1 : 0] ?? 'page';
                    return pageName === @js($paginationPage);
                });
                if (! paginating) return;
                clearTimeout(this.timer);
                this.timer = null;
                this.ready = false;
                this.pending = true;
                this.start();
                const finish = () => this.$nextTick(() => { this.pending = false; });
                succeed(finish);
                fail(finish);
            });
        },
        start() {
            if (this.ready || this.timer !== null) return;
            this.timer = setTimeout(() => { this.ready = true; this.timer = null; }, 650);
        },
        destroy() { clearTimeout(this.timer); this.unsubscribe?.(); },
    }" x-init="start(); @if ($paginationPage !== null) watchPagination(); @endif">
    <div wire:key="skeleton-visual-{{ $name }}" wire:ignore.self @if (! $initial) x-cloak @endif x-show="!ready || pending"
        role="status" aria-busy="true" x-bind:aria-busy="ready && !pending ? null : 'true'" aria-label="{{ $label }}">
        <div aria-hidden="true">{{ $skeleton }}</div>
        <span class="sr-only">Carregando…</span>
    </div>
    <div wire:key="skeleton-content-{{ $name }}" wire:ignore.self x-cloak x-show="ready && !pending">
        {{ $slot }}
    </div>
</div>
